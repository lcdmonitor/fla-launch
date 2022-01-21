<?php require('./includes/header.php'); ?>
<!--news Section-->
<section class="hero">
    <div class="hero__overlay"></div>
    <video playsinline="true" loop="loop" loading="lazy" autoplay="autoplay" muted="muted" class="hero__video">
        <source src="img/background.mp4" type="video/mp4">
    </video>
    <div class="h-100 container-custom position-relative news__content_container">
        <div class="d-flex h-100 align-items-center news__content-width">
            <div class="text-black bg-light p-5 rounded-3 news__content">
                <h1 class="news__heading">Teh News</h1>
                <p>Bringing North Florida one step closer to the stars...</p>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Inventore voluptatum earum possimus, ipsum atque sint, vel iusto impedit non architecto, tempora saepe quos quia id accusantium. Fugit nesciunt explicabo dolore!</p>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Distinctio dignissimos magni culpa labore dolor dolorum ipsam, totam eum debitis quo pariatur nihil omnis excepturi ullam magnam nostrum, reiciendis fugiat esse?</p>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Enim minima nesciunt provident quis quisquam modi asperiores sed eaque nobis quae impedit optio, quos adipisci hic. Sequi mollitia temporibus facere quia.</p>
                <button type="button" class="mt-2 btn btn-lg btn-outline-light" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    Coming Soon
                </button>
            </div>
        </div>
    </div>
</section>
<?php require('./includes/footer.php'); ?>