<?php require("./includes/header.php"); ?>
 
 <!-- coming soon modal-->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Coming Soon</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <!--<div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>-->
            </div>
        </div>
    </div>
    <!-- end coming soon modal-->


    <!--Hero Section-->
    <section class="hero">
        <div class="hero__overlay"></div>
        <video playsinline="true" loop="loop" loading="lazy" autoplay="autoplay" muted="muted" class="hero__video">
            <source src="img/background.mp4" type="video/mp4">
        </video>
        <div class="hero__content h-100 container-custom position-relative">
            <div class="d-flex h-100 align-items-center hero__content-width">
                <div class="text-white">
                    <h1 class="hero__heading">We are Florida Launch Alliance.</h1>
                    <p>Bringing North Florida one step closer to the stars...</p>
                    <button type="button" class="mt-2 btn btn-lg btn-outline-light" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        Coming Soon
                    </button>
                </div>
            </div>
        </div>

        <a href="#scroll-down" class="hero__scroll-btn">
            Explore <i class="bi bi-arrow-down-short"></i>
        </a>
    </section>
    <!--End Hero Section-->
    <a id="scroll-down"></a>
    <!-- Section 1 Start-->
    <section class="content_sections container-custom">
        <div class="row">
            <div class="col-12 col-sm-6 d-md-flex justify-content-md-center">
                <img src="img/shuttle.jpg" class="img-fluid pb-4 content_section_thumbnail" height="470" width="300"
                    loading="lazy" />
            </div>
            <div class="col-12 col-sm-6 align-self-center justify-content-md-center">
                <div class="content_sections__content-width">
                    <span>Mission:</span>
                    <h1 class="h2 mb-4">Launches</h1>
                    <p class="mb-4">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Autem inventore voluptatibus veritatis
                        neque excepturi animi rerum. Sint cumque voluptatum, dicta, nobis consequatur tenetur voluptates
                        dolorem odit voluptatibus, rem accusantium veritatis.
                    </p>
                    <a href="launch.php">Read More <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>
    <!-- Section 1 End-->
    <!-- Section 2 Start-->
    <section class="content_sections container-custom content--background">
        <div class="row">
            <div class="col-12 col-sm-6 d-md-flex justify-content-md-center order-sm-1">
                <img src="img/shuttle.jpg" class="img-fluid pb-4 content_section_thumbnail" height="470" width="300"
                    loading="lazy" />
            </div>
            <div class="col-12 col-sm-6 align-self-center justify-content-md-center">
                <div class="content_sections__content-width">
                    <span>Mission:</span>
                    <h1 class="h2 mb-4">Outreach</h1>
                    <p class="mb-4">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Autem inventore voluptatibus veritatis
                        neque excepturi animi rerum. Sint cumque voluptatum, dicta, nobis consequatur tenetur voluptates
                        dolorem odit voluptatibus, rem accusantium veritatis.
                    </p>
                    <a href="#">Read More <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>
    <!-- Section 2 End-->
    <!-- Section 3 Start-->
    <section class="content_sections container-custom">
        <div class="row">
            <div class="col-12 col-sm-6 d-md-flex justify-content-md-center">
                <img src="img/shuttle.jpg" class="img-fluid pb-4 content_section_thumbnail" height="470" width="300"
                    loading="lazy" />
            </div>
            <div class="col-12 col-sm-6 align-self-center justify-content-md-center">
                <div class="content_sections__content-width">
                    <span>Mission:</span>
                    <h1 class="h2 mb-4">High Power</h1>
                    <p class="mb-4">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Autem inventore voluptatibus veritatis
                        neque excepturi animi rerum. Sint cumque voluptatum, dicta, nobis consequatur tenetur voluptates
                        dolorem odit voluptatibus, rem accusantium veritatis.
                    </p>
                    <a href="#">Read More <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>
    <!-- Section 3 End-->
<?php require('./includes/footer.php'); ?>
