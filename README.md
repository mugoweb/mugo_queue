Mugo Queue
=

What is it?
-

It's a framework to handle a queue of tasks.

An example: You'd like to rebuild the eZ Find search index for all Article objects.
To re-index all articles, you add multiple tasks into the queue. With this framework, you can also
develop a task that can re-index a single article. Another process would fetch "re-index" tasks from
the queue, execute them, and then remove them if the execution was successful.

The framework comes with a script to add tasks to the queue or to execute tasks from the queue.
The task is an abstract class. To implement a concrete task, you need to implement 2 methods:

1. _function create( $parameters )_: 
The function has to create an array of task ids. With regard to the example, that function
would return an array of all article object ids.

2. _function execute( $task_id, $parameters )_:
Executes on single task id. With regard to the example, that function would a fetch the object
for the given $task_id and add it to the search index

Install instructions
-

1. extract the archive into the _extension_ folder
2. enable the extension -- for example in _settings/override/site.ini.append.php_

   Add this line:
   ActiveExtensions[]=mugo_queue
   
   under the block '[ExtensionSettings]'

3. Import DB schema:
   _mysql -u YourUser -pYourPass YourDB < sql/mysql/schema.sql_
   
3. re-generate autoloads:
   _php bin/php/ezpgenerateautoloads.php -e_

4. try to execute the script:
   _php extension/mugo_queue/bin/run.php_
   

What's the difference between Mugo Queue and an eZ Publish script or cronjob?
-

The end result is the same: a simple eZ Publish script can rebuild the eZ Find search index
for all Article objects - and so can Mugo Queue.

Mugo Queue requires you to divide the process into 2 steps:
1. adding the tasks to the queue
2. executing tasks from the queue

You can interupt a normal script at any time and restart it from scratch. With Mugo Queue
you have the option to continue at the point where you interrupted the script (with remaining
tasks in the queue).

You'd need to learn how to write a task class but you don't have to learn how to
implement a complete eZ Publish script or cronjob.

Mugo Queue supports multiple threads to process tasks from the queue.

Because of the 2 step process, you can collect tasks during the day and execute
the tasks at night (or at other low traffic times).

The Mugo Queue framework enables you to execute tasks not only from the command line
context but also in the context of a normal Apache request.

See also
-
http://www.mugo.ca/Blog/mugo-queue-ezpublish-tasks
