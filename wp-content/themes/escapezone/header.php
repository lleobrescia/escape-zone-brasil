<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7 no-js"  lang="pt-BR"  ng-app="escape">
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8 no-js" lang="pt-BR"  ng-app="escape">
<![endif]-->
<!--[if !(IE 7) & !(IE 8)]><!-->
<html class="no-js" lang="pt-BR" ng-app="escape">
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

<body <?php body_class(); ?> ng-controller="MainController as main">


  <?php
  //Get logo
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    $image = wp_get_attachment_image_src( $custom_logo_id , 'full' );

    //Get blog name
    $blog_title = get_bloginfo( 'name' );
  ?>

    <section class="header container-fluid">
     <div class="container">
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
          <h1><?php echo $blog_title; ?></h1>
          <?php endif ?>

          <button type="button" class="btn btn-primary" ng-class="{open:main.enableMenu}" ng-click="main.enableMenu = !main.enableMenu">
            <i class="fa fa-bars" aria-hidden="true" ></i>
          </button>
        </div>
        <!--col-sm-3-->
        <nav class="col-sm-8 nav" ng-show="main.enableMenu">
          <?php wp_nav_menu( array( 'theme_location' => 'header-menu' ) ); ?>
        </nav>
        <!--col-sm-9-->
      </div>
      <!--row-->
     </div>
     <!--container-->
    </section>
    <!--header-->
