
<?
include 'inc/init.php';

fAuthorization::requireLoggedIn();
$breadcrumbs[] = array('name' => 'Alerts', 'url' => 'index.php','active' => false);

$page_num = fRequest::get('page', 'int', 1);
$offset = ($page_num - 1)*$GLOBALS['PAGE_SIZE'];

$results = NULL;

$latest_alerts = 'SELECT c.check_id,name,r.status,count(c.check_id) as count, r.timestamp '.
                    'FROM subscriptions s '.
                    'JOIN checks c ON s.check_id = c.check_id '.
                    'JOIN check_results r ON s.check_id = r.check_id ';
if ($GLOBALS['DATABASE_TYPE'] == "postgresql") {
    $latest_alerts = $latest_alerts . 'WHERE r.timestamp >= NOW() - INTERVAL \'1 DAY\' ';
} else {
    $latest_alerts = $latest_alerts . 'WHERE r.timestamp >= DATE_SUB(CURDATE(),INTERVAL 1 DAY) ';
}
$latest_alerts = $latest_alerts .
                    'AND r.status IS NOT NULL '.
                    'AND acknowledged = 0 '.
                    'AND s.user_id = ' . fSession::get('user_id') . ' ' .
                    'GROUP BY c.check_id, r.status, r.timestamp ' .
                    'ORDER BY r.timestamp DESC ' .
                    'LIMIT ' . $GLOBALS['PAGE_SIZE'] . ' ' .
                    'OFFSET ' . $offset . ';';

$results = $db->query($latest_alerts);

$alert_count_query = 'SELECT COUNT(1) as count '.
                    'FROM subscriptions s '.
                    'INNER JOIN checks c ON s.check_id = c.check_id '.
                    'JOIN check_results r ON s.check_id = r.check_id ';
if ($GLOBALS['DATABASE_TYPE'] == "postgresql") {
    $alert_count_query = $alert_count_query . 'WHERE r.timestamp >= NOW() - INTERVAL \'1 DAY\' ';
} else {
    $alert_count_query = $alert_count_query . 'WHERE r.timestamp >= DATE_SUB(CURDATE(),INTERVAL 1 DAY) ';
}
$alert_count_query = $alert_count_query .
                    'AND r.status IS NOT NULL '.
                    'AND acknowledged = 0 '.
                    'AND s.user_id = ' . fSession::get('user_id') . ' ' .
                    'GROUP BY c.check_id;';
$alert_count = $db->query($alert_count_query)->countReturnedRows();

include 'inc/views/index.php';
