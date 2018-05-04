<?php
	define('USERNAME', 'PierreLeroy');
	define('PASSWORD', 'football53prose');
	define('PATH_TO_SVN', '~/Documents/ESEO/proSE/se2019-equipea2');
	define('NAME', 'Pierre Leroy');
	define('WIKI_NAME', 'Pierre_LEROY');

	require_once __DIR__.'/vendor/autoload.php';

	$client = new Redmine\Client('http://prose.eseo.fr/redmine/', USERNAME, PASSWORD);
	$time = $client->time_entry->all(['user_id' => 515, 'limit'=>1000])['time_entries'];
	$allIssues = array();
	$allOpenedIssues = $client->issue->all(['limit' => 1000, 'status_id'=>'open'])['issues'];
	$allClosedIssues = $client->issue->all(['limit' => 1000, 'status_id'=>'closed'])['issues'];

	// range
	foreach ($allOpenedIssues as $key => $issue) {
		$allIssues[$issue['id']] = $issue;
	}
	foreach ($allClosedIssues as $key => $issue) {
		$allIssues[$issue['id']] = $issue;
	}

	$issueToTime = array();
	$issueToDate = array();
	$parentToIssueId = array();
	$parentToSumTime = array();

	foreach ($time as $uniqueTime) {
		$parentId = $uniqueTime['issue']['id'];
		// get the parent issue
		while($parentId != null){
			// if parent exists and parent is not V1 (not precise enough)
			if(isset($allIssues[$parentId]['parent']['id']) && $allIssues[$parentId]['parent']['id'] != 29074){
				$parentId = $allIssues[$parentId]['parent']['id'];
			}else{
				break;
			}
		}
		if(!isset($parentToIssueId[$parentId]) || !in_array($uniqueTime['issue']['id'], $parentToIssueId[$parentId])){
			$parentToIssueId[$parentId][] = $uniqueTime['issue']['id'];
		}

		// list issues
		if(isset($issueToTime[$uniqueTime['issue']['id']])){
			$issueToTime[$uniqueTime['issue']['id']] += $uniqueTime['hours'];
		}else{
			$issueToTime[$uniqueTime['issue']['id']] = $uniqueTime['hours'];
		}

		// sum times
		if(isset($parentToSumTime[$parentId])){
			$parentToSumTime[$parentId] += $uniqueTime['hours'];
		}else{
			$parentToSumTime[$parentId] = $uniqueTime['hours'];
		}
	}

	// GET ALL COMMITS (already  ranged by order desc)
	$issueToLastCommit = array();
	chdir('/home/pierre/Documents/ESEO/proSE/se2019-equipea2');
	$commits = shell_exec('svn log --username '.USERNAME.' --password '.PASSWORD);
	$commits = explode('------------------------------------------------------------------------', $commits);
	foreach ($issueToTime as $issueId => $time) {
		$issueToLastCommit[$issueId] = '';
		foreach ($commits as $commit) {
			if(strpos($commit, '#'.$issueId)){
				$issueToLastCommit[$issueId] = explode(' ', $commit)[0];
				break;
			}
		}
	}

	$myWiki = '';

	$myWiki .= 'h2. '.NAME."\n\n";	
	foreach ($parentToIssueId as $parentId => $issueList) {
		$myWiki .= '> h3. '.$allIssues[$parentId]['subject'].' : '.$parentToSumTime[$parentId]."h\n\n";
		$myWiki .= '|_. Tâche |_. Date|_. Échéance |_. Travail effectué |_. Temps passé |_. Pourcentage du travail effectué |_. Révision |'."\n";
		foreach ($issueList as $key => $issueId) {
			$myWiki .= '|#'.$issueId.'| '.($allIssues[$issueId]['start_date'] ?? '').' | '.($allIssues[$issueId]['due_date'] ?? '').' | '.$allIssues[$issueId]['subject'].' | '.$issueToTime[$issueId].'h | '.($allIssues[$issueId]['done_ratio'] ?? '').'% | '.$issueToLastCommit[$issueId]." |\n";
		}
		$myWiki .= "\n\n";
	}
	$myWiki .= 'h2. Heures totales : '.array_sum($issueToTime).'h';

	$client->wiki->update('se2019-equipea2', WIKI_NAME, [
	    'text' => $myWiki,
	]);

	echo "[WIKI DONE]\n";