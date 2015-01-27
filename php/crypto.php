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
    'y' => 1.974,   'z' => 0.074,   ' ' => 10.0
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
  // XOR the two string and return the value
  // @param[in] rawstr1  String 1 in binary format
  // @param[in] rawstr2  String 2 in binary format
  // @return XOR-ed string. Return -1 on error.
  public function xor_str_str($rawstr1, $rawstr2)
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
  // XOR the string with the given integer
  // @param[in] rawstr  A string in binary format
  // @param[in] rawint  An int to xor in binary format
  // @return  A binary-string that has been XOR-ed 
  public function xor_str_int($rawstr, $rawint)
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

    // For each string...
    foreach ($input_list as $input_str)
    {
      foreach (range(0,88) as $key)
      {
        // Convert decimal to hex value(string) and pack() to binary format.
        // * http://stackoverflow.com/questions/5799399/php5-pack-is-broken-on-x84-64-env
        $bin_key = pack("H*", dechex($key));  // decbin()
        $bin_out = $this->xor_str_int($input_str, $bin_key);
        $output_str = implode(unpack("H*", $bin_out));  // bindec()

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

    return $top_list;
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
function s1c1()
{
  $hex_str = "49276d206b696c6c696e6720796f757220627261696e206c696b65206120706f69736f6e6f7573206d757368726f6f6d";
  $bin_str = pack("H*", $hex_str);  // hex2bin()
  echo "hex_str = $hex_str\n";
  echo "base2   = $bin_str\n";
  echo "base64 = " . base64_encode($bin_str) . "\n";
}

function s1c2()
{
  $algo = new XorAlgo();
  $str1 = "1c0111001f010100061a024b53535009181c";
  $str2 = "686974207468652062756c6c277320657965";

  // Convert hex chars to binary-format (2 hex chars: 0x00-0xFF)
  $bin1 = pack("H*", $str1);
  $bin2 = pack("H*", $str2);
  $bin3 = $algo->xor_str_str($bin1, $bin2);
  $str3 = implode(unpack("H*", $bin3));

  $length_str = strlen($str1);
  $length_bin = strlen($bin1);
  echo "length(str) = $length_str; length(bin) = $length_bin\n";
  echo "str1 = $str1\n";
  echo "str2 = $str2 = $bin2\n";
  echo "str3 = $str3 = $bin3\n";

}

function s1c3()
{
  $algo = new XorAlgo();
  $hex_str = "1b37373331363f78151b7f2b783431333d78397828372d363c78373e783a393b3736";
  $bin_str = pack("H*", $hex_str);
  echo "hex_input = $hex_str\n";
  echo "bin_input = $bin_str\n";


  $top_list = $algo->xorsearch_list_int(array($bin_str), 3);
  foreach ($top_list as $item) 
  {
    printf("key(%d);point(%d) = %s\n", $item->xor_byte, $item->point, hex2bin($item->output_str));
  }

}

////////////////////////////////////////////////////////////////////////////////
echo "--------------------\n";
echo "Set 1 Challenge 1\n";
s1c1();

echo "--------------------\n";
echo "Set 1 Challenge 2\n";
s1c2();

echo "--------------------\n";
echo "Set 1 Challenge 3\n";
s1c3();

echo "--------------------\n";
echo "Set 1 Challenge 4\n";



?>
