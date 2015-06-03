<?php


///////////////////////////////////////////////////////////////////////////////
//----------------------------------------------------------------------------
// A struct for storing information
class XorByteItem
{
  var $input_str  = "";
  var $xor_byte   = "";
  var $output_str = "";
  var $point = 0;
}

////////////////////////////////////////////////////////////////////////////////
class StringFreqJudge
{
  // http://en.wikipedia.org/wiki/Letter_frequency
  var $freq_table = Array(
    'a' => 8.167,   'b' => 1.492,   'c' => 2.782,
    'd' => 4.253,   'e' => 12.702,  'f' => 2.228,
    'g' => 2.015,   'h' => 6.094,   'i' => 6.966,
    'j' => 0.153,   'k' => 0.772,   'l' => 4.025,
    'm' => 2.406,   'n' => 6.749,   'o' => 7.507,
    'p' => 1.929,   'q' => 0.095,   'r' => 5.987,
    's' => 6.327,   't' => 9.056,   'u' => 2.758,
    'v' => 0.978,   'w' => 2.360,   'x' => 0.150,
    'y' => 1.974,   'z' => 0.074,   ' ' => 12.0
  );

  //----------------------------------------------------------------------------
  // Generate the point based on the alphabet frequency.
  // @param[in] instr  A string to measure.
  // @return  Point value, up to 3 decimal pt
  public function compute_point($instr)
  {
    $total_pt = 0;
    $input2 = strtolower($instr);
    foreach (str_split($input2) as $char)
    {
      // If it does not exist in the table, next!
      if (!array_key_exists($char, $this->freq_table)) {
        continue;
      }

      $total_pt += $this->freq_table[$char];
    }

    // Total point
    return $total_pt;
  }
}

////////////////////////////////////////////////////////////////////////////////
class XorAlgo
{
  //----------------------------------------------------------------------------
  // XOR the two binary strings and return the value
  // @param[in] rawstr1  String 1 in binary format
  // @param[in] rawstr2  String 2 in binary format
  // @return XOR-ed string. Return -1 on error.
  public function xor_exact($rawstr1, $rawstr2)
  {
    if (strlen($rawstr1) != strlen($rawstr2)) 
    {
      echo "Error: String length NOT equals";
      return -1;
    }

    $xor3 = Array();
    for($i=0; $i<strlen($rawstr1); $i++) 
    {
      $xor3[$i] = $rawstr1[$i] ^ $rawstr2[$i];
      //printf("a(%3d) ^ b(%3d) = %3d\n", 
      //  ord($rawstr1[$i]), ord($rawstr2[$i]), ord($xor3[$i]));
    }

    return implode($xor3);
  }

  //----------------------------------------------------------------------------
  // XOR each char in the string with the given integer
  // @param[in] rawstr  A string in binary format
  // @param[in] rawint  An int to xor in binary format
  // @return  A binary-string that has been XOR-ed 
  public function xor_each_int($rawstr, $rawint)
  {
    $output = Array();
    for($i=0; $i<strlen($rawstr); $i++)
    {
      $output[$i] = $rawstr[$i] ^ $rawint;
      //echo "raw(" . ord($rawstr[$i]) . ") ^ rawint(" . ord($rawint) . ") = " . ord($output[$i]) . "\n";
    }
    return implode($output);
  }


  //----------------------------------------------------------------------------
  // XOR the string with the given key
  // @param[in] rawstr  A binary string
  // @param[in] rawkey  A binary string to be the key
  // @return  A binary-string that has been XOR-ed 
  public function xor_repeating_key($rawstr, $rawkey)
  {
    $output = Array();
    $str_size = strlen($rawstr);
    $key_size = strlen($rawkey);

    # Error check
    if ($key_size<1) {
      echo "ERROR: Input rawkey has size < 1\n";
      return null;
    }

    # Loop and xor
    $index = 0;
    for($i=0; $i<$str_size; $i++)
    {
      $output[$i] = $rawstr[$i] ^ $rawkey[$index++ % $key_size];
    }

    return implode($output);
  }



  //----------------------------------------------------------------------------
  // Given an array of string(binary-format), XOR and search for ones 
  // closet to a real string.
  // @param[in] input_list  A list of string(binary format) to be xor
  // @param[in] max_candidate  The max amount of candidates to consider
  // @return  Top candidates of valid string
  public function xorsearch_list_int($input_list, $max_candidate) 
  {
    // Top candidate list, and min point to be in the list
    $judge = new StringFreqJudge();
    $top_list = Array();  // XorByteItem[]

    // Error check the size
    if (empty($max_candidate) || $max_candidate < 3) {
      $max_candidate = 3;
    }

    // For each string XOR with each integer 
    foreach ($input_list as $input_str)
    {
      foreach (range(0,255) as $key)
      {
        // Convert decimal to hex value(string) and pack() to binary format.
        // * http://stackoverflow.com/questions/5799399/php5-pack-is-broken-on-x84-64-env
        $bin_key = pack("H*", dechex($key));  // decbin()
        $bin_out = $this->xor_each_int($input_str, $bin_key);
        $output_str = implode(unpack("H*", $bin_out));  // bindec()

        // If contain non-printable chars, skip it
        $matches = array();
        if (preg_match_all("/[\x01-\x1F\x7F-\xFF]/", $bin_out, $matches) > 0) {
          continue;
        }

        // Compute the relevant point, and skip if not matching the standard.
        $point = $judge->compute_point($bin_out);

        // Create the item
        $item = new XorByteItem();
        $item->input_str  = $input_str;
        $item->xor_byte   = $key;
        $item->output_str = $output_str;
        $item->point = $point;

        // Update top_list
        $top_list = $this->add_to_top_list($top_list, $max_candidate, $item);
      }

    }

    return array_reverse($top_list);
  }

  //----------------------------------------------------------------------------
  // Logic to add a new element to top_list (or not)
  // @param[in] top_list  A list of XorByteItem with highest point
  // @param[in] max_candidate  The max amount of candidates to consider
  // @param[in] item  The element to compare with top_list
  // @return  Top candidates of highest point
  private function add_to_top_list($top_list, $max_candidate, $item)
  {
    $top_list_size = count($top_list);

    // (1) If top_list is empty, add and return.
    if ($top_list_size == 0) 
    {
      array_push($top_list, $item);
      return $top_list;
    }

    // (2) Search for the place to insert.
    //     Loop through until finding the place to insert.
    $curr_pos = 0;
    for ($i=0; $i<$top_list_size; $i++) 
    {
      if ($item->point <= $top_list[$i]->point) 
      {
        break;
      }

      $curr_pos++;
    }

    // (3) Otherwise, find the place to insert.
    // * If least qualify point, add to the start of top_list
    if ($curr_pos == 0) {
      array_unshift($top_list, $item);
    } 
    // * If highest point, add to the end of top_list
    else if ($curr_pos == $top_list_size) {
      array_push($top_list, $item);
    }
    // * Otherwise, split the array into two parts -- 0 to curr_pos, and curr_pos+1 to N
    else {
      $start = array_slice($top_list, 0, $curr_pos);
      $end   = array_slice($top_list, $curr_pos);
      $top_list = array_merge($start, array($item), $end);
    }

    // (4) If overfilled, remove the start of top_list
    if (count($top_list) > $max_candidate) {
      array_shift($top_list);
    }

    return $top_list;
  }


  
  //----------------------------------------------------------------------------
}
////////////////////////////////////////////////////////////////////////////////

class Convert
{

  //----------------------------------------------------------------------------
  // Convert hex string into base2 (binary).  Aka hex2bin()
  // @param[in] input  Hex string
  // @return  Text in base2
  public static function hexstr_to_base2($input) {
  	return pack("H*", $input);
  }

  //----------------------------------------------------------------------------
  // Convert hex string into base64
  // @param[in] input  Hex string
  // @return  Text in base64
  public static function hexstr_to_base64($input) {
  	return base64_encode(Convert::hexstr_to_base2($input));
  }
}




////////////////////////////////////////////////////////////////////////////////
function s1c1()
{
  echo "--------------------\n";
  echo "Set 1 Challenge 1\n";
  $hex_str = "49276d206b696c6c696e6720796f757220627261696e206c696b65206120706f69736f6e6f7573206d757368726f6f6d";
  echo "* hex_str = $hex_str\n";
  echo "* base2   = " . Convert::hexstr_to_base2($hex_str) . "\n";
  echo "* base64  = " . Convert::hexstr_to_base64($hex_str) . "\n";
}

//------------------------------------------------------------------------------

function s1c2()
{
  echo "--------------------\n";
  echo "Set 1 Challenge 2\n";
  $algo = new XorAlgo();
  $str1 = "1c0111001f010100061a024b53535009181c";
  $str2 = "686974207468652062756c6c277320657965";
  $str3 = "746865206b696420646f6e277420706c6179";
  $bin3Expected = hex2bin($str3);

  // Convert hex chars to binary-format (2 hex chars: 0x00-0xFF)
  $bin1 = hex2bin($str1);
  $bin2 = hex2bin($str2);
  $bin3Result = $algo->xor_exact($bin1, $bin2);
  $str3 = implode(unpack("H*", $bin3Result));

  $length_str = strlen($str1);
  $length_bin = strlen($bin1);
  echo "* Input...\n";
  echo " + length(str) = $length_str; length(bin) = $length_bin\n";
  echo "  + str1 (input)  = $str1 = $bin1\n";
  echo "  + str2 (input)  = $str2 = $bin2\n";
  echo "  + str3 (expect) = $str3 = $bin3Expected\n";

  echo "* Output...\n";
  if ($bin3Expected == $bin3Result) {
    echo "  + The answer MATCHED.\n";
  } else {
    echo "  + The answer DOES NOT MATECHED.  Result is '$bin3Result'\n";
  }
}

//------------------------------------------------------------------------------
function s1c3()
{
  echo "--------------------\n";
  echo "Set 1 Challenge 3\n";
  $algo = new XorAlgo();
  $hex_str = "1b37373331363f78151b7f2b783431333d78397828372d363c78373e783a393b3736";
  $bin_str = hex2bin($hex_str);
  echo "* hex_input = $hex_str\n";
  echo "* bin_input = $bin_str\n";

  echo "* Result...\n";
  $top_list = $algo->xorsearch_list_int(array($bin_str), 3);
  foreach ($top_list as $item) 
  {
    printf("key(%d);point(%d) = %s\n", $item->xor_byte, $item->point, hex2bin($item->output_str));
  }

}

//------------------------------------------------------------------------------
function s1c4()
{
  echo "--------------------\n";
  echo "Set 1 Challenge 4\n";
  $algo = new XorAlgo();

  # Read file as a map
  $filename = "4.txt";
  $entries  = file($filename,  FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  echo "* Input file name = '$filename'\n";


  # Convert the input into binary format
  foreach ($entries as &$value) {
    $value = hex2bin($value);
  }
  unset($value);

  # Search for the top candidates
  echo "* Result...\n";
  $top_list = $algo->xorsearch_list_int($entries, 3);
  foreach ($top_list as $item) 
  {
    printf("  + key(%d);point(%d) = %s\n", $item->xor_byte, $item->point, hex2bin($item->output_str));
  }

}

//------------------------------------------------------------------------------
function s1c5()
{
  echo "--------------------\n";
  echo "Set 1 Challenge 5\n";
  $algo = new XorAlgo();

  ## NOTE: make sure to use Unix EOL!! :)
  $input = <<<EOF
Burning 'em, if you ain't quick and nimble
I go crazy when I hear a cymbal
EOF;
  $answer = "0b3637272a2b2e63622c2e69692a23693a2a3c6324202d623d63343c2a26226324272765272a282b2f20430a652e2c652a3124333a653e2b2027630c692b20283165286326302e27282f";
  $xorkey = "ICE";  # act as a binary value

  # Printout
  echo "* Input string...\n$input\n";
  echo "* Repeating XOR key = '$xorkey'\n";
  echo "* Result...\n";

  $result_bin = $algo->xor_repeating_key($input, $xorkey);
  if ($result_bin == null) {
    echo "+ ERROR calling xor_repeating_key\n";
    return;
  }
  $result = bin2hex($result_bin);

  # Compare the output
  if ($result != $answer) {
    echo " + Expect = $answer\n";
    echo " + Result = $result\n";
  }
  else
  {
    echo " + Result matched = $result\n";
  }


}


////////////////////////////////////////////////////////////////////////////////
#s1c1();
#s1c2();
#s1c3();
#s1c4();

s1c5();

?>
