<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
sql('DROP TABLE pagingpro');
sql('DROP TABLE pagingpro_core_routing');
sql('DROP TABLE pagingpro_scheduler_events');
sql('DROP TABLE pagingpro_scheduler_range');
sql('DROP TABLE pagingpro_scheduler_exclusions');
//Delete all cron jobs
$sql = 'SELECT * FROM pagingpro_scheduler_crons';
$all_crons = sql($sql,'getAll',DB_FETCHMODE_ASSOC);
foreach($all_crons as $crons) {
    edit_crontab($crons['cron_name']);
}
sql('DROP TABLE pagingpro_scheduler_crons');

?>