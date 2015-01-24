
<pre>

<?php

print("hello world\n");


function s1c1()
{
  $hex_str = "49276d206b696c6c696e6720796f757220627261696e206c696b65206120706f69736f6e6f7573206d757368726f6f6d";
  $bin_str = pack("H*", $hex_str);
  echo "hex_str = " . $hex_str . "\n";
  echo "base2  = " . $bin_str . "\n";
  echo "base64 = " . base64_encode($bin_str) . "\n";
}

function xor_str($str_a, $str_b)
{
  $xor1 = gmp_init($str_a, 16);
  $xor2 = gmp_init($str_b, 16);
  $xor3 = gmp_xor($xor1, $xor2);
  return gmp_strval($xor3, 16);
}
function xor_int($bin_strin, $xint)
{
  $strin_list = str_split($bin_strin);
  $output = "";
  foreach ($strin_list as $strin_char)
  {
    echo "strin_char = $strin_char = " . ord($strin_char) . "\n";
    $output .= chr(ord($strin_char) ^ $xint);
  }
  return $output;
}

function char_freq($input)
{
  // Convert each into decimal, and up the count
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

function s1c3()
{
  $hex_str = "1b37373331363f78151b7f2b783431333d78397828372d363c78373e783a393b3736";
  $bin_str = pack("H*", $hex_str);
  echo "bin_str = $bin_str\n";

  $keys = range(0,255);
  foreach ($keys as $key)
  {
    $output = xor_int($bin_str, $key);

    //Check for amount of vowels...
    $matches = array();
    preg_match_all("/[aeiouy]/i", $output, $matches);
    $nvowels = count($matches[0]);

    preg_match_all("/[a-z0-9]/i", $output, $matches);
    $nchars = count($matches[0]);

    preg_match_all("/[\s]/", $output, $matches);
    $nspaces = count($matches[0]);

    preg_match_all("/[\x01-\x1F\x7F-\xFF]/", $output, $matches);
    $nnotprint = count($matches[0]);

    if ($nvowels>0 && $nspaces>0 && $nnotprint==0) {
      echo "output($key)($nvowels)($nchars)($nspaces)($nnotprint) = $output\n";
    }
  }

  $str = "ETAOIN SHRDLU";
  $output = xor_int($str, 88);
  echo "output = $output";
  //  E= 0100 0101
  // 88= 0101 1000
  //   =>0001 1101

}


echo "--------------------\n";
echo "Set 1 Challenge 1\n";
s1c1();

echo "--------------------\n";
echo "Set 1 Challenge 2\n";
$xor1 = "1c0111001f010100061a024b53535009181c";
$xor2 = "686974207468652062756c6c277320657965";
$xor3 = xor_str($xor1, $xor2);
echo "xor1 = " . $xor1 . "\n";
echo "xor2 = " . $xor2 . "\n";
echo "xor3 = " . $xor3 . "\n";

echo "--------------------\n";
echo "Set 1 Challenge 3\n";
s1c3();


?>
</pre>
