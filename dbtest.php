<?php require('./includes/functions.inc.php'); ?>
<?php require('./includes/bbcode.inc.php'); ?>


<?php

echo "Today is " . date("Y/m/d") . "<br>";

$parser = new JBBCode\Parser();
$parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());


$text = "The default codes include: [b]bold[/b], [i]italics[/i], [u]underlining[/u], ";
$text .= "[url=http://jbbcode.com]links[/url], [color=red]color![/color] and more.";

$parser->parse($text);

print $parser->getAsHTML();

print '<br>';

ListRoles();
?>