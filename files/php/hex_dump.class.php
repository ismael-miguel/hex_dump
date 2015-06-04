<?php

	final class HexDump {
		
		private $data = array();
		private $bytes = 0;
		private $lines = 0;
		private $output = array(
			'text'=>array(
				'header' => array(
					'h' => '|',
					'v' => '-'
				),
				'data' => array(
					'h' => '|',
					'v' => '-'
				),
				'footer' => array(
					'h' => '|',
					'v' => '='
				),
				'text' => array(
					'title' => '      TEXT      ',
					'invalid' => "\xEF\xBF\xBD",
					'non_printable' => '.'
				)
			),
			'html' => array(
				'tag' => 'table'
				'header' => 'th',
				'data' => 'td',
				'footer' => 'td'
			),
			'tex' => array(
				'class' => 'article'
			)
		);
		
		private static function _to_hex( $number ){
			$hex = strtoupper( dechex( $number ) );
			//if we don't check if the number is 0, it won't fill the whole space
			return ( $number === 0 || strlen( $hex ) & 1 ? '0' : '' ) . $hex;
		};
		
		public function __construct( $data )
		{
			switch( gettype( $value ) )
			{
				case 'string':
					
					foreach( str_split( $value, 16 ) as $k => $line )
					{
						$lines[$k] = array();
						
						for( $i = 0, $l = strlen($line); $i<$l; $i++ )
						{
							$lines[$k][$i] = self::_to_hex( ord( $line[$i] ) );
						}
					}
					
					break;
					
				case 'double':
				case 'integer':
				case 'boolean':
				case 'NULL':
					$lines[0] = str_split( self::_to_hex( $value ), 2 );
					break;
					
				case 'array':
					foreach( array_chunk( $value, 16, false ) as $k => $chunk )
					{
						foreach( $chunk as $k => $item )
						{
							switch( gettype( $item ) )
							{
								case 'double':
								case 'integer':
								case 'boolean':
								case 'NULL':
									if( $item > 255 )
									{
										trigger_error( 'hex_dump() numbers in a byte array cannot be higher than 255 on index ' . $k, E_USER_WARNING );
									}
									//we need to fix the number, if it isn't a single byte
									$chunk[$k] = self::_to_hex( $item & 255 );
									break;
								case 'string':
									if( strlen( $item ) > 1 )
									{
										trigger_error( 'hex_dump() strings in a byte array cannot have more than 1 character on index ' . $k, E_USER_WARNING );
									}
									//disregard the remaining of the string, since only the 1st char matters
									$chunk[$k] = self::_to_hex( ord( $item[0] ) );
									break;
								default:
									$chunk[$k] = '--';
									trigger_error( 'hex_dump() invalid value on index ' . $k, E_USER_WARNING );
							}
						}	
						$lines[] = $chunk;
					}
					break;
					
				default:
					trigger_error( 'Invalid value type passed', E_USER_WARNING );
			}
			
			$this->lines = count( $lines );
		}
		
		public function _generate_header( $type )
		{
			if( $type == 'html' )
			{
				$num_length = strlen( dechex( $line_count ) ) + 1;
				$num_length = $num_length + ( $num_length % 2 );
				
				$header = str_repeat( ' ',  $num_length ) . ' |';
				for( $number = 0; $number < 16; $number++ )
				{
					$header .= '0' . dechex( $number ) . '|';
				}
				$header .= '      TEXT      ';
				
				return  $header . PHP_EOL . str_repeat( '-', strlen( $header ) );
			}
		}
		
		public function render_html()
		{
			
		}
		
	}
