<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/includes/bbcode.inc.php'); ?>
    <!-- Section News Alert modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Section News Alert</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php
                    $newsAlertContent = GetNewsAlertContent();
                    if ($newsAlertContent) {
                        $parser = new JBBCode\Parser();
                        $parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
                        $parser->parse($newsAlertContent);
                        echo $parser->getAsHTML();
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <!-- end Section News Alert modal -->

    <!-- footer -->
    <footer class="footer bg-dark text-white">
        <div class="container-custom d-flex justify-content-between align-items-center py-3 border-highlight">
            <div class="col-md-4 d-flex aligh-items center">
                <a href="/" class="me-2 text-muted text-decoration-none">
                    <span>Florida Launch Alliance</span>
                </a>
            </div>
            <ul class="nav col-md-4 justify-content-end list-unstyled d-flex text-white">
                <li class="ms-3"><a href="https://twitter.com/flalaunch" class="text-muted" target="_blank"><i class="bi bi-twitter"></i> </a></li>
                <li class="ms-3"><a href="https://www.instagram.com/flalaunch/" class="text-muted" target="_blank"><i class="bi bi-instagram"></i> </a></li>
                <li class="ms-3"><a href="https://www.facebook.com/flalaunch/" class="text-muted" target="_blank"><i class="bi bi-facebook"></i> </a></li>
            </ul>
        </div>
    </footer>
    <!-- end footer-->
    <!-- bootstrap JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>
    <!-- custom js -->
    <script src="/js/site.js"></script>
</body>

</html>
