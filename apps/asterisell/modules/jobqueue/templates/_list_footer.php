<?php
use_helper('Markdown');
// Default culture is en_US
//
echo insertHelp('

## Log

This is a simple and persistent log of executed/pending jobs. 

A detailed description of encountered errors and problems is inside the "Problems" module.

## Always Scheduled Jobs

"Execute Jobs" button, execute both always scheduled jobs, and pending jobs.

## Jobs Execution

Typically jobs are executed from the cron-job. The advantage is that the php process have no the constraints of jobs executed on-line, and it can use a lot of memory, and time.

Jobs executed on-line, have typically a 30 second limit constraints, and memory constraints. So in case of heavy jobs you must press two or more time the "execute button", in order to execute different parts of a big job using different on-line sessions. 

## Check Call Cost Limits

After "Check Call Cost Limits", press "Execute Jobs" in order to process the discovered anomalies.

');
//}

?>