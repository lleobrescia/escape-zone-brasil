<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7 no-js"  lang="pt-BR" >
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8 no-js" lang="pt-BR" >
<![endif]-->
<!--[if !(IE 7) & !(IE 8)]><!-->
<html class="no-js" lang="pt-BR" >
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
