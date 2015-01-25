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
  var $input_str;
  var $xor_byte;
  var $output_str;

  var $vowel_count;
  var $alphanum_count;
  var $space_count;
  var $nonchar_count;
}

////////////////////////////////////////////////////////////////////////////////
class XorAlgo
{
  //----------------------------------------------------------------------------
  // public function xor_byte($input_str, $xor_byte) 
  // {

  //   //Check for amount of vowels...
  //   $matches = array();
  //   preg_match_all("/[aeiouy]/i", $output, $matches);
  //   $nvowels = count($matches[0]);

  //   preg_match_all("/[a-z0-9]/i", $output, $matches);
  //   $nchars = count($matches[0]);

  //   preg_match_all("/[\s]/", $output, $matches);
  //   $nspaces = count($matches[0]);

  //   preg_match_all("/[\x01-\x1F\x7F-\xFF]/", $output, $matches);
  //   $nnotprint = count($matches[0]);    
  // }

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
    }

    return implode($xor3);
  }

  //----------------------------------------------------------------------------
  // XOR the string with the given integer
  // @param[in] rawstr  A string of raw value
  // @param[in] xint  An integer to xor with
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
  private function char_freq($input)
  {
    // Convert each into decimal, and count..
    $counter = array();
    foreach (str_split($input,1) as $value)
    {
      if (array_key_exists($value, $counter)) {
        $counter[$value]++;
      } else {
        $counter[$value]=1;
      }
    }
    return $counter;
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


  $keys = range(0,0);
  foreach ($keys as $key)
  {
    $hex_key = pack("H*", dechex($key));
    $hex_out = $algo->xor_str_int($hex_input, $hex_key);


    $output = implode(unpack("H*", $hex_out));
    var_dump($hex_key);
    var_dump($hex_out);
    var_dump($output);


    //Check for amount of vowels...
    // $matches = array();
    // preg_match_all("/[aeiouy]/i", $output, $matches);
    // $nvowels = count($matches[0]);

    // preg_match_all("/[a-z0-9]/i", $output, $matches);
    // $nchars = count($matches[0]);

    // preg_match_all("/[\s]/", $output, $matches);
    // $nspaces = count($matches[0]);

    // preg_match_all("/[\x01-\x1F\x7F-\xFF]/", $output, $matches);
    // $nnotprint = count($matches[0]);

    // if ($nvowels>0 && $nspaces>0 && $nnotprint==0) {
    //   echo "output($key)($nvowels)($nchars)($nspaces)($nnotprint) = $output\n";
    // }
  }


}


echo "--------------------\n";
echo "Set 1 Challenge 1\n";
s1c1();

echo "--------------------\n";
echo "Set 1 Challenge 2\n";
s1c2();

echo "--------------------\n";
echo "Set 1 Challenge 3\n";
s1c3();


?>
