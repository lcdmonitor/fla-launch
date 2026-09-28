<?php
require($_SERVER['DOCUMENT_ROOT'] .'/includes/header.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/bbcode.inc.php');
$pageContent = GetSiteContent('education');
?>
<!--education Section-->
<section class="hero">
    <div class="hero__overlay"></div>
    <video playsinline="true" loop="loop" loading="lazy" autoplay="autoplay" muted="muted" class="hero__video">
        <source src="/img/background.mp4" type="video/mp4">
    </video>
    <div class="h-100 container-custom position-relative education__content_container">
        <div class="d-flex h-100 align-items-center education__content-width">
            <div class="text-black bg-light p-5 rounded-3 education__content">
                <h1 class="education__heading">Mission: Education</h1>
                <?php
                if ($pageContent) {
                    $parser = new JBBCode\Parser();
                    $parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
                    $parser->parse($pageContent);
                    echo $parser->getAsHTML();
                }
                ?>
                <button type="button" class="mt-2 btn btn-lg btn-outline-light" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    Coming Soon
                </button>
            </div>
        </div>
    </div>
</section>
<?php require($_SERVER['DOCUMENT_ROOT'] .'/includes/footer.php'); ?>
