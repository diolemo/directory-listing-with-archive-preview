<style> 

body { padding: 10px; line-height: 20px; font-size: 16px; } 
pre { margin: 0; }

</style>
<body>
<code>
<pre><?php

// using character class because the shell command
// from shell_exec() would match; exec is ok tho. 
$pid_private = shell_exec('pgrep -f [d]eluge-private');
$pid_public = shell_exec('pgrep -f [d]eluge-public');
$pid_autodl = shell_exec('pgrep -f [i]rssi-with-autodl');

if (isset($_GET['restart-deluge-private']))
{
	if ($pid_private) shell_exec(sprintf('kill %d; sleep 1', $pid_private));
	shell_exec('PATH=/home2/erinfox/bin:$PATH \
		nohup deluged-private >/dev/null 2>&1');
	die(header('Location: ?'));
}

if (isset($_GET['restart-deluge-public']))
{
	if ($pid_public) shell_exec(sprintf('kill %d; sleep 1', $pid_public));
	shell_exec('PATH=/home2/erinfox/bin:$PATH \
		nohup deluged-public >/dev/null 2>&1');
	die(header('Location: ?'));
}

if (isset($_GET['restart-autodl-irssi']))
{
	if ($pid_autodl) shell_exec(sprintf('kill %d; sleep 5', $pid_autodl));
	shell_exec('PATH=/home2/erinfox/bin:$PATH \
		nohup autodl-irssi daemon >/dev/null 2>&1');
	die(header('Location: ?'));
}

$bw = trim(shell_exec('checkmybw'));
$bw = preg_split('/\s+/', $bw);

$bwused = (float) $bw[0];
$bwtotal = (float) $bw[1];
$bwpercent = ($bwused / $bwtotal) * 100;
$bwused = $bwused / 1073741824;

$di = trim(shell_exec('quota | grep dev'));
$di = preg_split('/\s+/', $di);

$diused = (float) $di[1];
$ditotal = (float) $di[2];
$dipercent = ($diused / $ditotal) * 100;
$diused = $diused / 1048576;

printf("      bandwidth:   %0.2f GiB (%0.1f%%)\r\n", $bwused, $bwpercent);
printf("           disk:   %0.2f GiB (%0.1f%%)\r\n", $diused, $dipercent);
printf(" deluge-private:   % 5d (<a href=\"?restart-deluge-private\">restart</a>)\r\n", $pid_private);
printf("  deluge-public:   % 5d (<a href=\"?restart-deluge-public\">restart</a>)\r\n", $pid_public);
printf("   autodl-irssi:   % 5d (<a href=\"?restart-autodl-irssi\">restart</a>)\r\n", $pid_autodl);

?></pre>
</code>
</body>