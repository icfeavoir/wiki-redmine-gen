<?php
	$warnings = array();

	require_once __DIR__.'/vendor/autoload.php';
	require_once('consts.php');

	$client = new Redmine\Client('http://prose.eseo.fr/redmine/', USERNAME, PASSWORD);
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
	$issueToTotalTime = array();
	$issueToCollaborater = array();
	$issueToDate = array();
	$parentToIssueId = array();
	$parentToSumTime = array();

	foreach ($time as $uniqueTime) {
		// sum the time for this issue
		$allLoggedTimes = $client->time_entry->all([
		    'issue_id' => $uniqueTime['issue']['id'],
		    'limit'=>1000,
		]);
		$issueToTotalTime[$uniqueTime['issue']['id']] = 0;
		$issueToCollaborater[$uniqueTime['issue']['id']] = array();
		foreach ($allLoggedTimes['time_entries'] as $value) {
			$issueToTotalTime[$uniqueTime['issue']['id']] = $issueToTotalTime[$uniqueTime['issue']['id']]+$value['hours'];
			$user = $value['user']['name'];
			// if collab not in the array yet, we add it (not if it's me)
			if(!in_array($user, $issueToCollaborater[$uniqueTime['issue']['id']]) && $user != NAME){
				array_push($issueToCollaborater[$uniqueTime['issue']['id']], $user);
			}
		}

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

	// now we handle the parentToCreate array
	$parentToCreateId = 0;
	foreach ($parentToCreate as $parentToCreateName => $parentIssues) {
		// we go negative to be sure that we don't use an existing ID
		$parentToCreateId--;

		$parentToIssueId[$parentToCreateId] = array();
		$parentToCreateTime = 0;
		foreach ($parentIssues as $issueToGroup) {
			// if issueToGroup in not a parent, we ignore it (bug !)
			if(!array_key_exists($issueToGroup, $parentToSumTime)){
				array_push($warnings, 'Issue '.$issueToGroup.' is not a parent issue');
				break;
			}

			// sum times
			$parentToCreateTime = $parentToCreateTime + $parentToSumTime[$issueToGroup];
			
			// add all of it to created issues
			foreach ($parentToIssueId[$issueToGroup] as $key => $child) {
				array_push($parentToIssueId[$parentToCreateId], $child);
			}
			
			// finally remove from lists
			unset($parentToSumTime[$issueToGroup]);
			unset($parentToIssueId[$issueToGroup]);
		}
		$parentToSumTime[$parentToCreateId] = $parentToCreateTime;

		// create the fake parent issue
		$allIssues[$parentToCreateId] = array();
		$allIssues[$parentToCreateId]['subject'] = $parentToCreateName;
	}

	$myWiki = '';
	$myWiki .= 'h2. '.NAME."\n\n";	
	foreach ($parentToSumTime as $parentId => $totalTime) {
		$myWiki .= '> h3. '.$allIssues[$parentId]['subject'].' : '.$totalTime."h\n\n";
		$myWiki .= '|_. Tâche |_. Début |_. Travail effectué |_. Temps passé |_. Pourcentage du travail effectué |_. Révision |'."\n";
		$issueList = $parentToIssueId[$parentId] ?? [];	// security for bugs
		foreach ($issueList as $key => $issueId) {
			$myWiki .= 
				'|#'.$issueId.'| '.
				($allIssues[$issueId]['start_date'] ?? '').' | '.
				$allIssues[$issueId]['subject'].' | '.
				$issueToTime[$issueId].'h | '.
				(calcRatio($issueToTime[$issueId], $issueToTotalTime[$issueId])).'% '.
					(empty($issueToCollaborater[$issueId]) ? '' : '(').
					implode(', ', $issueToCollaborater[$issueId]).
					(empty($issueToCollaborater[$issueId]) ? '' : ')').'| '.
				implode(', ', $issueToCommits[$issueId])." |\n";
		}
		$myWiki .= "\n\n";
	}
	$myWiki .= 'h2. Heures totales : '.array_sum($issueToTime).'h';

	if(PUBLISH){
		$client->wiki->update('se2019-equipea2', WIKI_NAME, [
		    'text' => $myWiki,
		]);
		echo "[WIKI DONE]\n";
	}else{
		echo $myWiki;
	}

	echo "<br><br>---------------------WARNINGS------------------------<br><br>".implode("\n", $warnings);

	function calcRatio($my, $all){
		return round($my*100/$all);
	}