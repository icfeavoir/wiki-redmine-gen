<?php
	require_once __DIR__.'/vendor/autoload.php';
	require_once('consts.php');

	$client = new Redmine\Client('http://prose.eseo.fr/redmine/', USERNAME, PASSWORD);

	// $test = $client->time_entry->all([
	//     'issue_id' => 30700,
	//     'limit'=>1000,
	// ]);
	// ?><pre><?php
	// print_r($test);
	// ?></pre><?php
	// exit;

	$time = $client->time_entry->all(['user_id' => USER_ID, 'limit'=>1000])['time_entries'];
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
			// if parent exists and parent is in the not "not parent" list (not precise enough)
			if(isset($allIssues[$parentId]['parent']['id']) && !in_array($allIssues[$parentId]['parent']['id'], $parentToIgnore)){
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
	$issueToCommits = array();
	chdir(PATH_TO_SVN);
	$commits = shell_exec('svn log --username '.USERNAME.' --password '.PASSWORD.' --search '.USERNAME);
	$commits = explode('------------------------------------------------------------------------', $commits);
	foreach ($issueToTime as $issueId => $time) {
		$issueToCommits[$issueId] = array();
		foreach ($commits as $commit) {
			if(strpos($commit, '#'.$issueId)){
				$commitNumber = preg_replace("/[^A-Za-z0-9 ]/", "", explode(' ', $commit)[0]);
				// we add a coma only if not the first one
				array_push($issueToCommits[$issueId], $commitNumber);
			}
		}
	}

	// most important first
	arsort($parentToSumTime);

	$myWiki = '';
	$myWiki .= 'h2. '.NAME."\n\n";	
	foreach ($parentToSumTime as $parentId => $totalTime) {
		$myWiki .= '> h3. '.$allIssues[$parentId]['subject'].' : '.$totalTime."h\n\n";
		$myWiki .= '|_. Tâche |_. Début |_. Travail effectué |_. Temps passé |_. Pourcentage du travail effectué |_. Révision |'."\n";
		$issueList = $parentToIssueId[$parentId];
		foreach ($issueList as $key => $issueId) {
			$myWiki .= '|#'.$issueId.'| '.($allIssues[$issueId]['start_date'] ?? '').' | '.$allIssues[$issueId]['subject'].' | '.$issueToTime[$issueId].'h | '.($allIssues[$issueId]['done_ratio'] ?? '').'% | '.implode(', ', $issueToCommits[$issueId])." |\n";
		}
		$myWiki .= "\n\n";
	}
	$myWiki .= 'h2. Heures totales : '.array_sum($issueToTime).'h';

	$publish = true;

	if($publish){
		$client->wiki->update('se2019-equipea2', WIKI_NAME, [
		    'text' => $myWiki,
		]);
	}

	echo "[WIKI DONE]\n";