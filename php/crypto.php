<?php

///////////////////////////////////////////////////////////////////////////////
class XorStrItem
{
  var $input_str;
  var $xor_str;
  var $output_str;
}

class XorByteItem
{
  var $input_str  = "";
  var $xor_byte   = "";
  var $output_str = "";

  var $vowel_count    = 0;
  var $alphanum_count = 0;
  var $wspace_count   = 0;
  var $nonchar_count  = 0;
}

////////////////////////////////////////////////////////////////////////////////
class XorAlgo
{
  //----------------------------------------------------------------------------
  // XOR the two string and return the value
  // @param[in] rawstr1  String 1
  // @param[in] rawstr2  String 2
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
  // @param[in] rawstr  A string of raw value
  // @param[in] xint  A raw value to xor with
  // @return  A character-string of XOR-ed 
  public function xor_str_int($rawstr, $xint)
  {
    $output = Array();
    for($i=0; $i<strlen($rawstr); $i++)
    {
      $output[$i] = $rawstr[$i] ^ $xint;
      //echo "raw(" . ord($rawstr[$i]) . ") ^ xint(" . ord($xint) . ") = " . ord($output[$i]) . "\n";
    }
    return implode($output);
  }

  //----------------------------------------------------------------------------
  public function analyze_xor($input, $xint, $output) 
  {
    $retval = new XorByteItem();
    $retval->input_str  = $input;
    $retval->xor_byte   = $xint;
    $retval->output_str = $output;

    //Check for amount of vowels...
    $matches = array();
    preg_match_all("/[aeiouy]/i", $output, $matches);
    $retval->vowel_count = count($matches[0]);

    preg_match_all("/[a-z0-9]/i", $output, $matches);
    $retval->alphanum_count = count($matches[0]);

    preg_match_all("/[\s]/", $output, $matches);
    $retval->wspace_count = count($matches[0]);

    preg_match_all("/[\x01-\x1F\x7F-\xFF]/", $output, $matches);
    $retval->nonchar_count = count($matches[0]);

    return $retval;
  }

  //----------------------------------------------------------------------------
}

////////////////////////////////////////////////////////////////////////////////
function s1c1()
{
  $hex_str = "49276d206b696c6c696e6720796f757220627261696e206c696b65206120706f69736f6e6f7573206d757368726f6f6d";
  $bin_str = pack("H*", $hex_str);
  echo "hex_str = " . $hex_str . "\n";
  echo "base2  = " . $bin_str . "\n";
  echo "base64 = " . base64_encode($bin_str) . "\n";
}

function s1c2()
{
  $algo = new XorAlgo();
  $str1 = "1c0111001f010100061a024b53535009181c";
  $str2 = "686974207468652062756c6c277320657965";

  // Convert chars to 16bit hex (2 hex chars = 1 hex value)
  $hex1 = pack("H*", $str1);
  $hex2 = pack("H*", $str2);
  $hex3 = $algo->xor_str_str($hex1, $hex2);
  $str3 = implode(unpack("H*", $hex3));

  $length_str = strlen($str1);
  $length_bin = strlen($hex1);
  echo "length(str) = $length_str; length(hex) = $length_bin\n";
  echo "str1 = $str1\n";
  echo "str2 = $str2 = $hex2\n";
  echo "str3 = $str3 = $hex3\n";

}


function s1c3()
{
  $algo = new XorAlgo();
  $input = "1b37373331363f78151b7f2b783431333d78397828372d363c78373e783a393b3736";
  $hex_input = pack("H*", $input);
  echo "input  = $input\n";
  echo "packed = $hex_input\n";

  // Try xor-ing from 0 to 255
  $stat = Array();
  foreach (range(0,255) as $key)
  {
    // Convert decimal to hex value(string) and pack() to real hex-format
    // * http://stackoverflow.com/questions/5799399/php5-pack-is-broken-on-x84-64-env
    $hex_key = pack("H*", dechex($key));
    $hex_out = $algo->xor_str_int($hex_input, $hex_key);
    $output  = implode(unpack("H*", $hex_out));

    // var_dump($key);
    // var_dump($hex_out);
    // var_dump($output);

    // Analyze the output to see if it's a legit string.
    $item = $algo->analyze_xor($input, $key, $hex_out);

    // Use the most simple analysis -- some vowels and spaces
    if ($item->nonchar_count == 0 && $item->vowel_count > 0
    &&  $item->wspace_count > 0) 
    {
      array_push($stat, $item);
      printf("key(%d) = %s\n", $item->xor_byte, $item->output_str);
    }
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
