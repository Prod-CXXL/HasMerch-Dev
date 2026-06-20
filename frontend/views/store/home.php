<?php
/**
 * Store homepage view
 *
 * Hero section with video, headline, and CTAs.
 * CTA links use App::url() to match the PHP router.
 */
?>

<section id="hero" class="hero-compact">

    <div class="hero-media">
        <video autoplay muted loop playsinline poster="/assets/images/demo-poster.jpg">
            <source src="/assets/videos/card.mp4" type="video/mp4">
            <!-- .mov fallback for Safari if mp4 not yet available -->
            <source src="/assets/videos/card.mov" type="video/quicktime">
        </video>
    </div>

    <div class="hero-copy">
        <h1>Tap. Scan. Connect.</h1>

        <p class="lead">
            Give your fans an instant connection to your Linktree, Spotify, or stream.
        </p>

        <div class="hero-ctas">
            <a href="<?= App::url('shop') ?>" class="btn btn-primary">
                Shop Now
            </a>
            <a href="<?= App::url('about') ?>" class="btn btn-ghost">
                Learn More
            </a>
        </div>
    </div>

</section>
