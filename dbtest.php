<?php require($_SERVER['DOCUMENT_ROOT'] .'/includes/functions.inc.php'); ?>
<?php require($_SERVER['DOCUMENT_ROOT'] .'/includes/bbcode.inc.php'); ?>


<?php

echo "Today is " . date("l, Y/m/d") . "<br>";

$parser = new JBBCode\Parser();
$parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());


$text = "The default codes include: [b]bold[/b], [i]italics[/i], [u]underlining[/u], ";
$text .= "[url=http://jbbcode.com]links[/url], [color=red]color![/color] and more.";

$parser->parse($text);

print $parser->getAsHTML();

print '<br>';

ListRoles();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
SendEmail("dave@davidtsimmons.com","Dave Simmons", "Test Email from FLA Suppport","FLA was here in <b>Bold</b>", "Dave Was here");
}
?>

<form action="dbtest.php" method="POST"><input type="submit" value="Send Email"></form>