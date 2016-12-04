<?php
/**
* 
* pega as informações do menu contato
* o menu contato eh configurado pelo plugin advanced custom fields
*/

$face = get_field('facebook', 'option');
$twitter = get_field('twitter', 'option');
$instagram = get_field('instagram', 'option');
$google = get_field('google', 'option');
$linkedin = get_field('linkedin', 'option');
$contato = get_field('contato_info', 'option');
$horario = get_field('horario_info', 'option');

//custom post ( ver function.php)
$args = array( 'post_type' => 'faq' ); 
$loop = new WP_Query( $args );
$count = 0; //Usado para mudar o ID dos panels
?>

<section class="faq container-fluid">
  <div class="container">
    <h2 class="text-center">FAQ?</h2>
    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
    <?php while ( $loop->have_posts() ) : $loop->the_post();  $count++;?>
       <div class="panel panel-faq">
        <div class="panel-heading" role="tab" id="heading<?php echo $count; ?>">
          <h4 class="panel-title">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $count; ?>" aria-expanded="false" aria-controls="collapse<?php echo $count; ?>">
             <i class="fa" aria-hidden="true"></i> <?php the_title(); ?>
            </a>
          </h4>
        </div>
        <!--panel-heading-->
        <div id="collapse<?php echo $count; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?php echo $count; ?>">
          <div class="panel-body">
            <?php the_field( "resposta", get_the_ID() ); ?>
          </div>
          <!--panel-body-->
        </div>
        <!--panel-collapse-->
      </div>
      <!--panel-faq-->
      <?php endwhile; ?>
    </div>
    <!--panel-group-->
  </div>
  <!--container-->
</section>
<!--faq-->
<footer class="footer container-fluid">
  <div class="container">
    <div class="row">
      <div class="col-sm-4">
        <h4>SIGA!</h4>

        <ul class="social">
          <?php if ($face): ?>
          <li>
            <a href="<?php echo $face; ?>" target="_blank">
              <i class="fa fa-facebook" aria-hidden="true"></i>
            </a>
          </li>
          <?php endif ?>
          <?php if ($twitter): ?>
          <li>
            <a href="<?php echo $twitter; ?>" target="_blank">
              <i class="fa fa-twitter" aria-hidden="true"></i>
            </a>
          </li>
          <?php endif ?>
          <?php if ($instagram): ?>
          <li>
            <a href="<?php echo $instagram; ?>" target="_blank">
              <i class="fa fa-instagram" aria-hidden="true"></i>
            </a>
          </li>
          <?php endif ?>
          <?php if ($google): ?>
          <li>
            <a href="<?php echo $google; ?>" target="_blank">
              <i class="fa fa-google-plus" aria-hidden="true"></i>
            </a>
          </li>
          <?php endif ?>
          <?php if ($linkedin): ?>
          <li>
            <a href="<?php echo $linkedin; ?>" target="_blank">
              <i class="fa fa-linkedin" aria-hidden="true"></i>
            </a>
          </li>
          <?php endif ?>
        </ul>
      </div>
      <!--col-sm-4-->
      <div class="col-sm-4">
        <h4>CONTATO</h4>
        <?php
          if($contato)
          echo $contato; 
        ?>
      </div>
      <!--col-sm-4-->
      <div class="col-sm-4">
        <h4>Horário de Funcionamento</h4>
        <?php
          if($contato)
          echo $contato; 
        ?>
      </div>
      <!--col-sm-4-->
    </div>
    <!--row-->
  </div>
  <!--container-->
</footer>

<div class="copy container-fluid">
  <div class="container">
    <small>
     	&copy; Copyright - ESCAPE BRASIL - by  <a href="http://8dpropaganda.com.br/" target="_blank" title="8D Propaganda">8D Propaganda</a>
    </small>
  </div>
  <!--container-->
</div>
<!--copy-->

<?php wp_footer(); ?>
</body>

</html>