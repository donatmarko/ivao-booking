<?php 
if (isset($_GET['code']) && isset($_GET['state'])) {
    header('Location: ../../newlogin_ivao.php?code='. $_GET['code'] . '&state=' . $_GET['state']);
} else {
    header('Location: ../../newlogin_ivao.php');
}
