<?php
require($_SERVER['DOCUMENT_ROOT'] . '/includes/header.php');
require($_SERVER['DOCUMENT_ROOT'] . '/includes/bbcode.inc.php');
?>
<!--launch Section-->
<section class="hero">
    <div class="hero__overlay"></div>
    <video playsinline="true" loop="loop" loading="lazy" autoplay="autoplay" muted="muted" class="hero__video">
        <source src="/img/background.mp4" type="video/mp4">
    </video>
    <div class="h-100 container-custom position-relative launch__content_container">
        <div class="d-flex h-100 align-items-center launch__content-width">
            <div class="text-black bg-light p-5 rounded-3 launch__content">
                <?php
                $pageid = $_GET["pageid"];

                $page = GetPageContent($pageid);

                if (!$page) {
                    $title = "Error";
                } else {
                    $title = $page["Title"];
                }
                ?>

                <h1 class="launch__heading"><?php print($title) ?></h1>
                <!-- TODO Update CSS Classes, these refer to launch page -->
                <!-- TODO Clean this up, maybe inline some of this logic -->
                <?php
                $parser = new JBBCode\Parser();
                $parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());



                if (!$page) {
                    print "404: Content Not Found";
                } else {
                    $members_only = boolval($page["MemberOnly"]);

                    if ($members_only && !GetIsUserLoggedIn()) { ?>
                        <p>You must be logged in as a member to view this content, please <a style="text-decoration: underline; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></p>
                <?php } else {
                        $parser->parse($page["Content"]);

                        print $parser->getAsHTML();
                    }
                }
                ?>
            </div>
        </div>
</section>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'); ?>