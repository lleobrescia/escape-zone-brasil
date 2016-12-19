<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7 no-js"  lang="pt-BR">
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8 no-js" lang="pt-BR">
<![endif]-->
<!--[if !(IE 7) & !(IE 8)]><!-->
<html class="no-js" lang="pt-BR">
<!--<![endif]-->

<head>
  <meta charset="utf-8">
  <base href="<?php $url_info = parse_url( home_url() ); echo trailingslashit( $url_info['path'] ); ?>">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <?php wp_head(); ?>
  <!--[if lt IE 9]>
      <script src="https://cdn.jsdelivr.net/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://cdn.jsdelivr.net/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body <?php body_class(); ?>>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v2.8";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

  <?php
  //Get logo
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    $image = wp_get_attachment_image_src( $custom_logo_id , 'full' );

    //Get blog name
    $blog_title = get_bloginfo( 'name' );
  ?>

    <section id="header" class="header container-fluid">
      <div class="container">
        <div class="row">
          <div class="col-xs-12 top-bar">
             <?php if ( is_user_logged_in() ) : ?>
              <a class="minha-conta" href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="Minha Conta">Minha Conta</a>
           <?php else: ?>
              <a class="minha-conta"href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="Login">Login</a>
           <?php endif ?>
            <span class="divisor">|</span>
            <?php if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) : ?>

            <?php  $count = WC()->cart->cart_contents_count; ?>
            <a class="cart-contents" href="<?php echo WC()->cart->get_cart_url(); ?>" title="<?php _e( 'View your shopping cart' ); ?>">
              <i class="fa fa-shopping-cart" aria-hidden="true"></i> Carrinho
              <?php if ( $count > 0 ) : ?>
              <span class="cart-contents-count"><?php echo esc_html( $count ); ?></span>
              <?php else: ?>
              <span class="cart-contents-count">0</span>
              <?php endif ?>
            </a>

            <?php endif ?>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-4 logo">

            <?php
          /**
          * Se existir logo adiciona ela. 
          * Se nao, escreve o nome do site
          */
          ?>

              <?php if ($image[0]): ?>
              <a href="<?php $url_info = parse_url( home_url() ); echo trailingslashit( $url_info['path'] ); ?>" title="<?php echo $blog_title; ?>">
            <img src="<?php echo $image[0]; ?>" alt="<?php echo $blog_title; ?>">
          </a>
              <?php else: ?>
              <h1>
                <?php echo $blog_title; ?>
              </h1>
              <?php endif ?>

              <button id="menu" type="button" class="btn btn-primary">
                <i class="fa fa-bars" aria-hidden="true" ></i>
              </button>
          </div>
          <!--col-sm-3-->
          <nav id="nav" class="col-sm-8 nav mobile-hidden">
            <?php wp_nav_menu( array( 'theme_location' => 'header-menu' ) ); ?>
          </nav>
          <!--col-sm-9-->
        </div>
        <!--row-->
      </div>
      <!--container-->
    </section>
    <!--header-->