<?php require('./includes/functions.inc.php'); ?>
<?php require('./includes/bbcode.inc.php'); ?>
<?php printSiteName(); ?>

<?php ListUsers(); ?>

<?php
$parser = new JBBCode\Parser();
$parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());

$parser->parse(htmlentities("[B]Dave Was Here[B]"));

print $parser->getAsHtml();
?>