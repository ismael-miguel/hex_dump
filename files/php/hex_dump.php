<?php

	function hex_dump( $value )
	{
		$start_time = microtime(true);
		
		switch( gettype( $value ) )
		{
			case 'string':
			
				$lines = array_map(
						function( $line ){
							return array_map(
									function( $char ){
										return str_pad( dechex( ord( $char ) ), 2, 0, STR_PAD_LEFT );
									},
									str_split( $line )
								);
						},
						str_split( $value, 16 )
					);
				break;
				
			case 'double':
			case 'integer':
				
				$lines = array(
						array_map(
							function( $digits ){
								return str_pad( $digits, 2, 0, STR_PAD_LEFT );
							},
							str_split( dechex( $value ), 2 )
						)
					);
				break;
				
			case 'array':
			
				$lines = 
					array_map(
						function( $chunk ){
							return array_map(
									function( $item ){
										switch( gettype( $item ) )
										{
											case 'double':
											case 'integer':
												return str_pad( dechex( $item & 255 ), 2, 0, STR_PAD_LEFT );
											case 'string':
												return str_pad( dechex( ord( $item ) ), 2, 0, STR_PAD_LEFT );
											default:
												return '--';
										}
									},
									$chunk
								);
						},
						array_chunk( $value, 16, false )
					);
				break;
				
			default:
				trigger_error( 'Invalid value type passed', E_USER_WARNING );
				return false;
		}
		
		$num_length = strlen( dechex( $line_count = count( $lines ) ) ) + 1;
		
		$header = str_repeat( ' ', $num_length = $num_length + ( $num_length % 2 ) ).
			' |'.
			implode(
				'|',
				array_map(
					function( $number ){
						return str_pad( strtoupper( dechex( $number ) ), 2, 0, STR_PAD_LEFT );
					},
					range( 0, 15 )
				)
			).
			'|      TEXT      ';
		
		echo $header, PHP_EOL;
		
		$separator = str_repeat( '-', strlen( $header) );
		
		foreach( $lines as $current_line => &$line )
		{
			$line_lenth = count( $line );
			
			echo
				$separator,
				PHP_EOL,
				str_pad( strtoupper( dechex( $current_line ) ), $num_length - 1, 0, STR_PAD_LEFT ),
				'0 |',
				strtoupper(
					implode(
						'|',
						$line_lenth < 16
							?array_pad(
								array_merge(
									$line,
									array_fill(0, 16 - $line_lenth, '  ')
								),
								16,
								null
							)
							:$line
					)
				),
				'|',
				implode(
					'',
					array_map(
						function( $value ){
							if( $value == '--' )
							{
								return "\xBF";
							}
							else
							{
								$value =  hexdec( $value );
								
								return $value < 32 || $value > 126 ? '.' : chr( $value );
							}
						},
						$line
					)
				),
				PHP_EOL;
		}
		
		$stats = array(
			'lines' => $line_count,
			'bytes' => $line_count ? ( $line_count * 16 ) - ( 16 - count( $lines[ $line_count - 1 ] ) ) : 0,
			'time' => microtime(true) - $start_time
		);
		
		echo 
			str_repeat( '=', strlen( $header) ),
			PHP_EOL,
			str_pad( 'Lines: ' . $stats['lines'], 15, ' '),
			'| ',
			str_pad( 'Bytes: ' . $stats['bytes'], 16, ' '),
			'| Time: ',
			$stats['time'],
			'ms',
			PHP_EOL,
			PHP_EOL;
		
		return $stats;
	}
