<?php
// ----------------- referrals ------------------
function referStats()
{
  // last 24 hours stats
  global $db, $log_table;

  $sql = "select refer, landing, upd from {$log_table} 
				where upd >= date_sub(now(),interval 23 hour) and refer is not null 
        order by upd desc";
  $res = $db->query($sql);

  while ($e = $res->fetch_row()) {
    $refs[] = $e[0];
    $land[] = $e[1];
    $upd[] = $e[2];
  }

  $last = deriveDay(strtotime($upd[0]));

  echo "<h3>Referrals $last <span class='btns'><div class='button' data-go=''>back</div></span></h3><table id='stats' class='sttable'>\n";
  $users = 0;
  echo "<thead><tr><th data-sort='string'>▽ Referral</th><th data-sort='string'>▽ Landing</th>
		<th data-sort='string'>▽ Time</th></tr></thead><tbody>\n";
  $sep = 1;
  $u = date('Y-m-d');
  $i = 0;
  foreach ($refs as $ref) {
    $users++;
    $class = '';
    if ($sep && strpos($upd[$i], $u) === false) {
      $sep = 0;
      $class = "sep";
    }
    printf(
      "<tr class='drill-down %s'><td>%s</td><td>%s</td><td>%s</td></tr>\n",
      $class,
      $ref,
      $land[$i],
      substr($upd[$i], 11)
    );
    $i++;
  }
  printf("</tbody><tfoot><tr><td>Sessions: %d</td><td></td><td></td></tr></tfoot></table>\n", $users);

  # most referrals
  $sql = "select refer as r, count(*) as c from {$log_table} 
				where refer is not null and upd >= date_sub(now(),interval 50 day) group by r order by c desc";

  $res = $db->query($sql);
  $words = [];
  while ($e = $res->fetch_row()) {
    $words[$e[0]] = $e[1];
  }
  arsort($words);

  echo "<h3>&nbsp;</h3><h3>Top referrals last 50 days <span class='btns'><div class='button' data-go=''>back</div></span></h3><table id='stats' class='sttable'>\n";
  echo "<thead><tr><th data-sort='string'>▽ referral</th><th data-sort='int' data-sort-default='desc'>▽ clicks</th></tr>
			</thead><tbody>\n";

  $i = 0;
  foreach ($words as $k => $w) {
    if ($i++ > 50) {
      break;
    }
    printf("<tr><td>%s</td><td>%d</td></tr>\n", $k, $w);
  }
  printf("</tbody><tfoot><tr><td></td><td></td></tr></tfoot></table>\n");

  return true;
}
