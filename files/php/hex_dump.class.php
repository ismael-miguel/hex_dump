<?php

	class HexDump {
		
		private $data = array();
		private $bytes = 0;
		private $lines = 0;
		
		static function _to_hex( $number ){
			$hex = strtoupper( dechex( $number ) );
			//if we don't check if the number is 0, it won't fill the whole space
			return ( $number === 0 || strlen( $hex ) & 1 ? '0' : '' ) . $hex;
		};
		
		public function __construct( $data )
		{
			switch( gettype( $data ) )
			{
				case 'string':
					
					foreach( str_split( $data, 16 ) as $k => $line )
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
					$lines[0] = str_split( self::_to_hex( $data ), 2 );
					break;
					
				case 'array':
					foreach( array_chunk( $data, 16, false ) as $k => $chunk )
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
			
			$this->data = $lines;
			
			if( $this->lines > 1 )
			{
				$this->bytes = ( $this->lines - 1 ) * 16;
			}
			
			$this->bytes += count( end( $lines ) );
		}
		
		public function get_data(){
			return $this->data;
		}
		
		public function get_lines(){
			return $this->lines;
		}
		
		public function get_bytes(){
			return $this->bytes;
		}
	}
	
	final class HexDump_Text_Render {
		private $cache = array();
		private $chars = array(
			'default' => array(
				'header' => array( 'v' => '|', 'h' => '-' ),
				'data' => array( 'v' => '|', 'h' => '-' ),
				'footer' => array( 'v' => '|', 'h' => '=' ),
				'text' => array( 'title' => '      TEXT      ', 'invalid' => "\xEF\xBF\xBD", 'non_printable' => '.' )
			),
			'basic' => array(
				'header' => array( 'v' => ' ', 'h' => ' ' ),
				'data' => array( 'v' => ' ', 'h' => ' ' ),
				'footer' => array( 'v' => ' ', 'h' => ' ' ),
				'text' => array( 'title' => '      TEXT      ', 'invalid' => "\xEF\xBF\xBD", 'non_printable' => '.' )
			)
		);
		private $hexdump;
		
		public function __construct(HexDump $hexdump){
			$this->hexdump = $hexdump;
		}
		
		public function render( $style = 'default' ){
			
		}
		
		public function __toString(){
			
		}
		
	}
	
/*
		function hex_dump( $value )
	{
		
		$to_hex = function( $number ){
			$hex = strtoupper( dechex( $number ) );
			//if we don't check if the number is 0, it won't fill the whole space
			return ( $number === 0 || strlen( $hex ) & 1 ? '0' : '' ) . $hex;
		};
		$lines = array();
		$start_time = microtime(true);
		
		switch( gettype( $value ) )
		{
			case 'string':
				
				foreach( str_split( $value, 16 ) as $k => $line )
				{
					$lines[$k] = array();
					
					for( $i = 0, $l = strlen($line); $i<$l; $i++)
					{
						$lines[$k][$i] = $to_hex( ord( $line[$i] ) );
					}
				}
				
				break;
				
			case 'double':
			case 'integer':
			case 'boolean':
			case 'NULL':
				$lines[0] = str_split( $to_hex( $value ), 2 );
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
								$chunk[$k] = $to_hex( $item & 255 );
								break;
							case 'string':
								if( strlen( $item ) > 1 )
								{
									trigger_error( 'hex_dump() strings in a byte array cannot have more than 1 character on index ' . $k, E_USER_WARNING );
								}
								//disregard the remaining of the string, since only the 1st char matters
								$chunk[$k] = $to_hex( ord( $item[0] ) );
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
				return false;
		}
		
		$line_count = count( $lines );
		
		$num_length = strlen( dechex( $line_count ) ) + 1;
		$num_length = $num_length + ( $num_length % 2 );
		
		$header = str_repeat( ' ',  $num_length ) . ' |';
		for( $number = 0; $number < 16; $number++ )
		{
			$header .= '0' . dechex( $number ) . '|';
		}
		$header .= '      TEXT      ';
		
		echo $header, PHP_EOL;
		
		$separator = str_repeat( '-', strlen( $header ) );
		
		foreach( $lines as $current_line => &$line )
		{
			echo $separator, PHP_EOL;
			
			//the number must be padded with 0s in the beginning, to the size of the highest line number
			echo str_pad( strtoupper( dechex( $current_line ) ), $num_length - 1, '0', STR_PAD_LEFT ),'0 |';
			
			//outputs what is in the line, regardless of the length
			echo implode( '|', $line ), '|';
			
			//we need to fix the missing spaces in the output
			$missing = 16 - count( $line );
			if( $missing > 0 )
			{
				do
				{
					echo '  |';
				}
				while( --$missing );
			}
			
			foreach( $line as $value )
			{
				if( $value == '--' )
				{
					// replacement character, for invalid values on byte arrays
					echo "\xEF\xBF\xBD";
				}
				else
				{
					$value =  hexdec( $value );
					
					echo $value < 32 || $value > 126 ? '.' : chr( $value );
				}
			}
			
			echo PHP_EOL;
		}
		
		$stats = array(
			'lines' => $line_count,
			//if there isn't a check to see if we have any line, this will cause errors
			'bytes' => $line_count ? ( $line_count * 16 ) - ( 16 - count( $lines[ $line_count - 1 ] ) ) : 0,
			'time' => microtime(true) - $start_time
		);
		
		echo str_repeat( '=', strlen( $separator ) ), PHP_EOL;
		echo str_pad( 'Lines: ' . $stats['lines'], 15, ' '), '| ';
		echo str_pad( 'Bytes: ' . $stats['bytes'], 16, ' '), '| ';
		echo 'Time: ', $stats['time'], 'ms', PHP_EOL, PHP_EOL;
		
		return $stats;
	}
	
	final class HexDump {
		
		private $data = array();
		private $bytes = 0;
		private $lines = 0;
		private static $output = array(
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
		
		private $cache = array();
		private $line_width = 90;//width of the line, in chars
		
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
		
		//only useful for text output
		private static function _generate_text_line( $char )
		{
			//ensures a correct width and looks better on repeated patterns
			return str_pad( $char, $this->line_width, $char, STR_PAD_RIGHT ) . PHP_EOL;
		}
		
		private function _generate_header( $type )
		{
			if( $type == 'text' )
			{
				if( isset( $this->cache['text'] ) )
				{
					return $this->cache['text']['header'];
				}
				
				$num_length = strlen( dechex( $line_count ) ) + 1;
				$num_length = $num_length + ( $num_length % 2 );
				
				$header = str_repeat( ' ',  $num_length ) . ' ' . self::$output['text']['header']['h'];
				for( $number = 0; $number < 16; $number++ )
				{
					$header .= '0' . dechex( $number ) . self::$output['text']['header']['h'];
				}
				$header .= self::$output['text']['text']['title'];
				
				$this->line_width = strlen( $header );
				$this->cache['text']['header'] = $header . PHP_EOL . $this->_generate_text_line( self::$output['text']['header']['v'] );
				
				unset( $header );//no longer needed
				
				return $this->cache['text']['header'];
			}
		}
		
		private function _generate_line( $type, $data, $line )
		{
			if( $type == 'text' )
			{
				$text = $this->_generate_text_line( self::$output['text']['data']['v'] );
				
				//the line number must be padded with 0s in the beginning, to the size of the highest line number
				$text .= str_pad( strtoupper( dechex( $line ) ), strlen( dechex( $this->lines ) ), '0', STR_PAD_LEFT ) . '0 ' . self::$output['text']['data']['h'];
				
				//outputs what is in the line, regardless of the length
				$text .= implode( self::$output['text']['data']['h'], $line ) . self::$output['text']['data']['h'];
				
				//we need to fix the missing spaces in the output
				$missing = 16 - count( $line );
				if( $missing > 0 )
				{
					do
					{
						$text .= '  ' . self::$output['text']['data']['h'];
					}
					while( --$missing );
				}
				
				return $line;
			}
		}
		
		public function render_text()
		{
			
		}
		
		public function render_html()
		{
			
		}
		
	}
*/
