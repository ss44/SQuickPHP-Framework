<?php
require_once('../simpleFunctions.php');

echo "Testing Random Chars\n";
echo "(1) with numbers.. and case sensitive:\n";

for ($x = 0; $x < 10; $x++ ){
	$y = rand(1, 10);
	echo randomChars( $y )."\n";
}

echo "\n\nNot Case Sensitive:\n";
for ($x = 0; $x < 10; $x++ ){
	$y = rand(1, 10);
	echo randomChars( $y, true, false )."\n";;
}


echo "\n\nNo Numbers CS\n";
for ($x = 0; $x < 10; $x++ ){
	$y = rand(1, 10);
	echo randomChars( $y, false )."\n";
}

echo "\n\nNo Numbers nCS\n";
for ($x = 0; $x < 10; $x++ ){
	$y = rand(1, 10);
	echo randomChars( $y, false, false )."\n";
}

?>