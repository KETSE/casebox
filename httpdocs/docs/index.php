<?php include('../header.php') ?>

<br>
<a href="/"><img src="/i/casebox-logo-medium.png" alt="CaseBox Logo" title="CaseBox Logo" /></a>
<br><br>
<h1 style="border-bottom: 1px solid #CCC">CaseBox Documentation</h1>
<br />

<h3>Contents</h3>
<ul>
<li><a href="#intro">1. Introduction</a></li>
<li><a href="#users">2. Offices, Managers and Users</a>
<ul><li><a href="#users-out-office">2.1. Users that doesn't belong to any office</a></li></ul>
</li>

<li><a href="#case">3. The Case</a>
  <ul>
  <li><a href="#case-actions">3.1. Actions</a></li>
  <li><a href="#case-files">3.2. Files</a>
    <ul><li><a href="#case-actions-files">3.2.1. Files of an action</a></li></ul>
  </li>
  <li><a href="#case-objects">3.3. Objects</a></li>
  <li><a href="#case-objects">3.4. Tasks</a></li>
  <li><a href="#case-objects">3.5. Phases</a></li>
  <li><a href="#case-access">3.6. Case access</a></li>
  </ul>
</li>
<li><a href="#thesaurus">4. Thesaurus</a></li>
<li><a href="#tags">5. Tags</a></li>
<li><a href="#tpl">6. Templates</a></li>
<li><a href="#langs">7. Languages</a></li>
</ul>


<br /><br />
<h3><a name="intro"></a>1. Introduction <span class="label label-important">todo</span></h3>
<p>dashboard screenshot</p>

<br /><br />
<h3><a name="users"></a>2. Offices, Managers and Users</h3>

<p>An empty CaseBox instance will have only one user: the Administrator, and no offices, managers or users. Let's define these terms:</p>

<ul>
<li><p><img style="vertical-align: top" src="/i/ico/user-business-boss.png" alt="Administrator" /> <b>Administrator</b>: the user that has access to all information in CaseBox. His role is to configure the system (manage templates, thesauries, offices), create managers and assign them to offices.</p></li>

<li><p><img style="vertical-align: top" src="/i/ico/user-red.png" alt="Manager" /> <b>Manager</b>: the user that has access to all information in his office/offices. His role is to manage users of his office, create cases and assign responsible users for cases.</p></li>

<li><p><img style="vertical-align: top" src="/i/ico/user-gray.png" alt="User" /> <b>User</b>: the actual lawyer working in one or several offices.</p></li>

<li><p><img style="vertical-align: top" src="/i/ico/user-white.png" alt="User out of office" /> <b>User outside offices</b>: a special type of user that doesn't belong to any office. (See section 2.1.)</p></li>

<li><p><img style="vertical-align: top" src="/i/ico/building-old.png" alt="Office" /> <b>Office</b>: an office is comprised of a bunch of cases and a list of users that work with them. By default, a user that works in an office <b>is able to see all cases</b> that belongs to his office (i.e. read access).</p></li>
</ul>


<p>Suppose <b>Legal Process</b> is an international organization with 3 offices in <span class="lb lb-office">London</span>, <span class="lb lb-office">Moscow</span> and <span class="lb lb-office">New York</span>. Each office has its lawyers and cases. To setup this, the Administrator will first create offices and add a manager per office. Then each manager will be required to add the users of his office to the system. Below we graphically illustrate the process, notice that <span class="lb lb-manager">Frederick Baker</span> has added two users that doesn't belong to any office.</p>

<div class="scr">
<img alt="" src="/i/docs/users2.png" />
<p>Figure 1. Administrator creates Managers. Managers create Users.</p>
</div>

<p>Figure 2 shows the User Management screenshot made from <span class="lb lb-admin">Administrator</span> account.</p>

<div class="scr">
<img alt="Users, Administrator account" src="/i/docs/f2-users-admin-view.png" />
<p>Figure 2. User Management as seen from Administrator account.</p>
</div>


<p>Figure 3 is made from <span class="lb lb-manager">Edwin Jackson</span> account. Notice the <i>Other offices</i> folder for Edwin: he can't create users there, but is allowed to add them to his own office/offices. Notice the start of the dragging process: <span class="lb lb-user">Darryl Barley</span> is being added to the <span class="lb lb-office">New York</span> office by Edwin.</p>
<div class="scr">
<img alt="Users, Manager account" src="/i/docs/f3-dnd-user-to-office-bd-info.png" class="nb" />
<p>Figure 3. Manager 'Edwin Jackson' adds 'Darryl Barley' to his office.</p>
</div>


<p><span class="label label-important">Important</span> 
Only Administrator and Managers are able to make changes to the list of CaseBox users. A Manager can create/delete users only inside his office/offices. The Manager may also allow access to users from other offices to his office (to do this he needs to drag and drop a user to his office, or change the value of <i>Manager</i>, <i>User</i> columns in the grid).
</p>

<p><span class="label label-info">Info</span> Users created by the logged in Manager (or Administrator) are colored in blue. It means that each user in the system has the Creator who added him. A user can be deleted only by the manager who created him (or by the Administrator of course).
</p>

<br />
                
<h4><a name="users-outoffice"></a>2.1. Users that doesn't belong to any office</h4>
<p>It was mentioned that a user is able to see all cases from his office. But there are situations when a lawyer that works on a case would like to invite an external lawyer to work collaboratively. There is a need to add a user to the system that is able to see only one (or several) selected cases. CaseBox allows to add a user that doesn't belong to any office.
</p>

<p>Suppose that <span class="lb lb-user">Edgar Cloud</span> needs to invite an external lawyer <span class="lb lb-user-out">Johny Hannon</span> to work on a case.
<br /> <i>Edgar</i> should ask his office manager to add the user <i>Johny Hannon</i> and give him access to a specific case only. Edgar's manager is <span class="lb lb-manager">Edwin Jackson</span>.
<br />
Figure 4 illustrates how Edwin adds Johny, notice the <i>Office</i> field, it's value is <i>Out of office</i>. To see how a user is given access to a specific case, refer to <a href="#case-access">Case access control</a>.</p>

<div class="scr">
<img alt="Manager adds an out of office user." src="/i/docs/f4-adding-user.png"  />
<p>Figure 4. Manager <i>Edwin Jackson</i> creates <i>Johny Hannon</i>.</p>
</div>

<p>All users that are not assigned to one of the offices are grouped together in a folder called <span class="lb lb-office-out">Users out of office</span> and a white icon is used to display them, notice the newly added <span class="lb lb-user-out">Johny Hannon</span> in Figure 5. Johny doesn't have any access rights for now (he will see no cases if logs in).

<div class="scr">
<img alt="User out of office" src="/i/docs/f5-user-out-office.png"  />
<p>Figure 5. Manager 'Edwin Jackson' added 'Johny Hannon' that doesn't belong to any office.</p>
</div>
<br />
<br />




<h3><a name="users"></a>3. The Case</h3>
<p>A case is comprised of several things:</p>
<table class="table table-bordered">
<tbody>

<tr><td><b>Actions</b></td><td>it's actually the Correspondence of a case. The Action is at the heart of a case structure. An action is something that happens during a case lifecycle: a complaint is sent, a decision is received, an email the laywer sends to his client etc. There are different types of actions, each described by a custom set of fields. An Action usually contains one or more files. You may perceive an action as an envelope for the files you're adding to the case. An envelope on which custom attributes are mentioned (like type of decision, the authority that issued it etc.)</td></tr>

<tr><td style="width: 10%"><b>Files</b></td><td>uploaded files (PDF, DOC) are indexed for full text search. You can upload an archive(ZIP) and CaseBox will extract files automatically.</td></tr>

<tr><td><b>Objects</b></td><td>The applicants and perpetrators of a case, or some forms that needs to be filled in for the case. Technically an Object is similar to an Action: it's a list of custom fields.</td></tr>

<tr><td><b>Tasks</b></td><td>Users may assign tasks to each other: the main lawyer of a case creates a task for the secondary lawyer for ex. CaseBox Dashboard displays all active tasks assigned or created by the user. Tasks may have deadlines. Reminders can be set to notify responsible users (users assigned to the task) about the approaching deadline.</td></tr>

<!-- <tr><td><b>Phases</b></td><td>a case lifecycle may be splitted into several phases. There may be specific Action types per phase. Think of phases as if there are tags attached to a case, depending on the associated tags, the list of available actions change. Phases a tightly connected to the tagging system described below.</td></tr> -->


<!-- <tr><td><b>Activities</b></td><td><span class="label label-important">to be implemented</span> An activity is what/when and how long has been done. It's used to track laywer's time spent on a case to calculate the cost.</td></tr> -->

<!-- <tr><td><b>Messages</b></td><td><span class="label label-important">to be implemented</span> Users may send messages to each other.</td></tr> -->
</tbody>
</table>

<!--
<p>
The picture below illustrates a case. Notice the action list (top-right) and custom action fields (type, who made decision) in the action preview panel (bottom-right).
</p>

<div class="scr">
<img alt="Case" src="/i/docs/f6-case-overview-desc.png" class="nb" />
<p>Figure 6. The case.</p>
</div>
<br />

<h3><a name="users"></a>3.1. Actions</h3>
<p>There two main categories of Actions: <b>Incoming</b> and <b>Outgoing</b>:
<ul>
<li><b>Incoming</b>: everything that comes from "outside": a court's decision, an email from the applicant, even a phone call is perceived as an incoming action.</li>
<li><b>Outgoing</b>: everything you send.</li>
</ul>

<p>
Both Incoming and Outgoing actions may be of different types. An action type is defined by a list of custom fields that describes the action. We may also call it an action template. Templates are discussed in section 5. Figure 7 shows an action with two custom fields (Type, Who made decision).
There are two basic action fields: the date when the action was performed and the title. Each action type may have a title template: i.e. the title will be generated automatically based on custom fields.
</p>

<p>A custom field (Property) has a Value, an additional text and attached files.</p>

<div class="scr">
<img alt="Case" src="/i/docs/f7-case-action.png" />
<p>Figure 7. Case action.</p>
</div>

<p>
<span class="label label-info">Info</span> You may think of actions as "envelopes" for files. The envelope is like a form with a set of fields. An action is a core element in CaseBox and offers functionality like tasks, relationships between actions. You'll not be able to add a task or write a message for a file for ex, create an action first.
</p>
<br />


<h3><a name="files"></a>3.2. Files</h3>
<p>
Normally files are added inside actions, but it's also possible to attach files directly to the case itself. For ex. you'd like to keep some research reports along with your case. Click the arrow icon <img src="/i/ico/external.png" /> located on the right of "Files" block to open Case Files of the case.
</p>

<p>
You can upload one o several files, or even upload an archive and CaseBox will extract the files. And vice verca: you may select some files and download them archived in one ZIP file.
For PDF/DOC files, the system determines the number of pages and displays it on the right side of the filename. 
</p>


<p>
CaseBox is not using the traditional folder system because it has limitations: how to keep a file in two folders at the same time? The tagging approach is used instead. You may filter the list of files based on one or several tags.
</p>

<p>
The chain icon <img src="/i/ico/chain.png" /> shows that the file belongs to an action (or several actions). The gray tags means that these tags were inherited from the action. i.e. the file itself may have it's own tags along with the tags it gots from the action attached to.
</p>

<div class="scr">
<img alt="Case files" src="/i/docs/f8-case-files-desc.png" class="nb" />
<p>Figure 8. Case files manager.</p>
</div>

<p>Click the <span class="bt bt-edit">Edit</span> button to change file properties:
<ul>
<li><b>File</b>: replace the file with another one</li>
<li><b>Title</b>: a human readable title may be given to a file if the filename doesn't tell what the file is about.</li>
<li><b>Date</b>: when the file was produced. Notice that it's not the date when the file was uploaded to the system. By default, when a file is attached to an action, the date of the action is used for the file.</li>
<li><b>Common tags</b>: </li>
<li><b>Personal tags</b>: each CaseBox user has it's own list of tags in addition to the list of common tags seen by everyone.</li>
</ul>
</p>
<div class="scr">
<img alt="Editing the properties of a file" src="/i/docs/f9-file-properties.png"  />
<p>Figure 9. Editing the properties of a file.</p>
</div>




<br />
<h4><a name="users"></a>3.2.1. Files of an action.</h3>

<p>An action usually has one attached file, it's called <b>main file</b>, and eventually a set of additional attachments. Figure 10 shows a request by the applicant, 
the word document is the main file, while two draft versions (1 alpha, 2 beta) are represented as additional files. Notice how attached files inherit action's tags, in this case it's "domestic".
</p>

<div class="scr">
<img alt="Action files" src="/i/docs/f10-action-files-desc.png" class="nb" />
<p>Figure 10. Action files.</p>
</div>
<br />


-->
<br />
<h4><a name="users"></a>3.2.2. Bulk upload (zip files) & Multiple upload.</h3>

<p>When you have lots of files to upload, it's more convenient (and faster) to archive all your files locally and upload a single ZIP archive.
CaseBox will automatically extract all files. To do this, select "Upload archive" submenu item in Case Files window:
</p>

<div class="scr">
<img alt="Action files" src="/i/docs/f10_1-bulk-upload.png" class="" />
<p>Figure 10.1. Bulk Upload (ZIP archive).</p>
</div>

<p>Another option is to select "Upload multiple files", a dialog will appear, allowing you to attach several files at once.</p>

<br />



<h3><a name="objects"></a>3.3. Objects</h3>
<p>
Objects have been introduced to CaseBox to allow custom information to be entered for a case or to connect with other elements of CaseBox system.
Some examples:
<ul>
<li>A list of custom fields that describe a case is called a Case Card, that's one of the basic objects available.
</li>
<li>The lifecycle of a case may be comprised of several phases. Each phase may have it's own list of fields that describes it. For ex: the ECHR phase will have the start/end dates.</li>
<li>Adding a person from the Contacts Database as a client (applicant) or perpetrator to a case is done through an object.</li>
</ul>
</p>

<p>
Technically, objects are similar to actions: a list of custom fields. Files can be attached. 
</p>

<p>
In the screenshots above there are two objects for the Mangouras v. Spain case: <span class="lb lb-case-card">Case card</span> and <span class="lb lb-echr">ECHR</span>.
</p>

<p><span class="label label-info">hint</span> The ECHR object is used to describe the case at ECHR level in general. Along with this object there are actions during ECHR phase, but these contain specific custom fields related to the action itself and not the phase as a whole. 
</p>

<div class="scr">
<img alt="Case" src="/i/docs/f11-case-object-echr.png" />
<p>Figure 11. Custom fields of a case object.</p>
</div>

<!-- 

<h3><a name="object-contact"></a>3.3.1. Adding applicants and perpetrators to the case</h3>
<p>
Because an applicant or perpetrator may appear in more than one case, when they are added to a case, it's a completely new object, i.e. the same applicant is created several times as a normal objects (i.e. just a bunch of fields) and there is no link between them. Using other words: instead of having only one applicant in the system and linking him to cases, each case has its own "copy" of the applicant's record. The problem here is that:
<ul>
<li>we can't determine how many applicants we have in our system.</li>
<li>updating the information about a contact in one case, doesn't propagate to other cases.</li>
</ul>

The solution was to create a separate <a href="#contactdb">Contacts Database</a>: a person is entered once and then linked many times to cases as an applicant/perpetrator or any other role.
</p>

<p>The process of creating contacts and linking them to a case is described in <a href="#contactdb-object">Section 4.2.</a></p>

-->

<br />
<br />
<h3><a name="case-tasks"></a>3.4. Tasks</h3>
<p>
Tasks were introduced to help the lawyer manage the deadlines for the his cases, what/when he has to do. It will also help office managers to track what a lawyer in his office have to do.
</p>

<p>
A task has the following main properties:
<ul>
<li><b>Creator</b>: the user who created the task. (If the task has a deadline and it's missed, the creator will be charged with this. i.e. He has to supervise the Responsible Users he assignes for the task.)</li>
<li><b>Responsible users</b>: a list of users that should complete the task. The task will be marked as completed when ALL users will accomplish it.</li>
<li><b>Deadline</b>: the date by which the task should be completed. It's an optional field.</li>
<!-- <li><b>Critical</b>: Is this an <b>internal</b> (non-crictical) or <b>external</b> task (critical). Internal tasks, like "scan this document" or "sign this paper" usually doesn't affect the organization, we may also call them "soft" deadlines. External tasks are imposed by courts or other mechanisms, like ECHR for ex: they send letters and require an answer to be sent by a specific date. Missing such a deadline usually has serious implications.<br>
(the label "critical" is to be discussed, maybe a better word can be found. What is clear is that we should delimit/tag important deadlines.)</li> -->
<li><b>Title</b>: what actually has to be done</li>
<li><b>Description</b>: a more detailed definition of the task</li>
</ul>
</p>

<p>
A task can be created for a case or for a specific action of case. To add a task, click the <span class="lb lb-task">Add Task</span> button in the Action's toolbar. A popup window appears. Notice that by default you're creating a task for the selected action. In our case the action is <span class="lb lb-decision">Constitutional Court declared the appeal inadmissible</span>. That gives us 6 months to prepare an application for ECHR as an example.
</p>

<div class="scr">
<img alt="Case" src="/i/docs/f3-4_add-task-txt.png" class="nb" />
<p>Figure 3.4.1 Adding a task for an action.</p>
</div>

<p>
By default, the Responsible user is user who is actually logged in. Click the button at the right of the field and a small window with users appears. The system displays all users from the same office. If logged in user belongs to several offices, all users from those offices will be displayed. In other words: a user can't assign a task for another one whom he "doesn't know".
</p>

<div class="scr">
<img alt="Adding a task: selecting responsible users" src="/i/docs/f3-4_task-users.png" class="nb" />
<p>Figure 3.4.2. Assigning responsible users for the task.</p>
</div>

<p>The user should have read access to the case in order to add a task. It means that a user is able to create tasks for any case in his office. 
We don't expect it to happen, but we mention this in the documentation for the sake of clarity.</p>

<p>When the task is created by clicking Create button, the system will send email notifications to all responsible users about this new task that has been assigned to them.</p>

<!-- 


<h4><a name="task-advanced"></a>3.4.1. Tasks: advanced properties</h4>
<p>
The following task properties were classifed as advanced due to seldom use:
<ul class="li-pb10">
<li><b>Autoclose</b>: if autoclose is true, the task will close when the last responsible user will accomplish the task. Sometimes the creator of the task needs to review the work done before closing the task, in this case he should uncheck it when creating the task (he can later edit it as well).</li>
<li><b>Parent tasks</b>: the task need to be accomplished before parent tasks will start. i.e. first dependant tasks should be closed, and this will allow parent tasks to start. This feature allows a more complex workflow to be configured in CaseBox. An example: User1 creates Task1 for User2 and User3, but to accomplish the task, User2 needs the help of User4. In this case User2 creates Task2 for User4 and selects Task1 as a parent task for Task2.</li>
<li><b>Responsible party</b>: usually it's Self, i.e. the organization itself is responsible for the task, but there are situations when we need to track external parties, like courts, ECHR or other mechanisms. An example of such a task: we are sending a complaint to the court, usually the court should reply in a predefined number of days (according to law). We may create a task "check court's reply in 10 days" and assign the court as responsible party.</li>
<li><b>Private</b>: only task creator see the task.</li>
</ul>
</p>

<div class="scr">
<img alt="Advanced task properties" src="/i/docs/f3-4_add-task-advanced.png" class="nb" />


<p>Figure 3.4.3. Advanced task properties.</p>
</div>

<h4><a name="task-browser"></a>3.4.2. Case tasks</h4>
<p>
If a tasks is linked with an action, a calendar icon <img src="/i/ico/calendar-small-gray.png"> is displayed after the action title. Selecting an action will load a preview at the bottom. The preview lists the tasks associated with the action. Click the task to complete it (if you're one of the responsible users or edit/delete it (if you're the creator of the task).
</p>

<div class="scr">
<img alt="Action tasks" src="/i/docs/f3_4_3-action-tasks-txt.png" class="nb" />
<p>Figure M. Working with case/action tasks.</p>
</div>

<p>The total number of tasks (how many active, closed, missed deadlines) are displayed on the left side under Files block. Click the Tasks block label to view all case tasks.</p>

<div class="scr">
<img alt="Browsing case tasks" src="/i/docs/f3_4_4-case-task-browser-txt.png" class="nb" />
<p>Figure M. Browsing case tasks.</p>
</div>


<h4><a name="task-browser"></a>3.4.3. Task browser</h4>
<p>A user can see latest tasks created by him or assigned to him in the dashboard. To see all the tasks, click the blue arrow at the top right in the Tasks toolbar in dashboard.</p>

<div class="scr">
<img alt="Task Browser" src="/i/docs/f3_4_5-task-browser.png" />
<p>Figure M. Task Browser</p>
</div>

<p>Below the task title additional task info is displayed: The case and action associated with the task, who created it and the list of responsible users. Open tasks are colored in <span style="color: black"><b>black</b></span>, accomplished tasks but not yet closed: <span style="color: green"><b>green</b></span>, closed tasks: <span style="color: gray"><b>gray</b></span>. Notice: accomplised but not yet closed tasks are the ones with Autoclose set to No.</p>
<div class="scr">
<img alt="Browsing case tasks" src="/i/docs/f3_4_6-task-list-props-txt.png" class="nb" />
<p>Figure M. Task properties.</p>
</div>


<br /><br />
<h3><a name="case-phases"></a>3.5. Phases <span class="label label-important">todo</span></h3>
<p>An example of phases list: Domestic litigation, ECHR. Describe the relationship between phases and available actions for a case...</p>

<br /><br />
<h3><a name="case-access"></a>3.6. Case Access</h3>
<p>Cases are created only by Office Managers.</p>
<p>Suppose that the <span class="lb lb-office">London</span>'s manager <span class="lb lb-manager">Frederick Baker</span> creates a case <span class="lb lb-case">Mangouras v. Spain</span> and assigns <span class="lb lb-user">Darryl Barley</span> as the lawyer for the case.</p>

<p>The laywer of a case is able to upload/modify/delete case data. Technically speaking, he has <i>write access to the case</i>.</p>


<div class="scr">
<img alt="Case" src="/i/docs/create-case.png" />
<p>Figure 12. Creating a case and assigning the main lawyer.</p>
</div>

<p><span class="label label-info">Note</span> By default, all users of the office the case belongs to, will have read acceess to the case, i.e. will be able to open and see case data.</p>

<p>A manager can create cases only inside his offices (Notice that a user can be manager in several offices). As the main responsible lawyer for the case he can choose any user inside selected office.</p>

<p>There are situations when the default access privileges are not enough:
<ul>
<li>More then one user needs to have write access for the case. Typical example: there is one main laywer and an assistant lawyer.</li>
<li>Temporary read/write access is needed for a specific user. Before the main lawyer leaves for holiday, he may ask his manager to assign a user for the case for a predefined period of time.
</li>
</ul>

<p>Only the manager is able to change access privileges to a case. For this he should open the case and click "Case Access".</p>

<div class="scr">
<img alt="Case access" src="/i/docs/f13-case-privileges-button.png" />
<p>Figure 13. Opening case access page.</p>
</div>

<p>
The Access control tab displays all users within CaseBox (on the left) and the list of offices/users that have access to the case (on the right). <span class="lb lb-office">London</span> and <span class="lb lb-user">Darryl Barley</span> were added by CaseBox when Mangouras v. Spain case has been created (the manager specified the Office and User at that time).
</p>

<div class="scr">
<img alt="Case Access Manager" src="/i/docs/f14-case-access.png" />
<p>Figure 14. Managing access to a case.</p>
</div>


<p>
Let's allow offices/users to access the case:
</p>
<ul class="li-pb10">
<li><span class="lb lb-office">Moscow</span> is given read access since 01.08.2012. Notice: managers of this office will also have only read access. Use "Read for all + write by managers" access type to give control to managers.</li>
<li><span class="lb lb-user">Edgar Cloud</span> has been given Read access in the past, but the manager decided to disable his access.</li>
<li><span class="lb lb-user-out">Johny Hannon</span>, a user that doesn't belong to any office, will have write access until 06.07.2012.</li>
</ul>

<div class="scr">
<img alt="Case Access Manager" src="/i/docs/f14_1-case-access.png" />
<p>Figure 15. Managing access to a case.</p>
</div>
<br /><br />
-->

<!--

<h3><a name="contactdb"></a>4. Contacts Database</h3>
<p>
A case may contain applicants, perpetrators, judges who made decisions etc. We call all of them Contacts. A contact may appear in more than one case, thus a  centralized list of all contacts is needed. A contact has a custom form with fields like name, email, birthday (the list of fields is customizable).
</p>

<p>Relationships may be created between contacts. A good example is an organization (a court) and the list of its judges. CaseBox allows to define several types of contacts. The Organization is just another type of contact (it may be inconvenient to perceive Organizations as contacts, but such approach makes the sytem flexible as explained later). The figure below shows the list of contacts: one contact is being edited <span class="lb lb-office">Corcubión (Corunna) no. 1</span>.
</p>

<div class="scr">
<img alt="" src="/i/docs/f4_1-contacts-db.png" />
<p>Figure 4.1. Managing access to a case.</p>
</div>


<h4><a name="contactdb-action"></a>4.1. Action fields linked to Contacts Database</h4>
<p>In the example above, suppose we add a list of courts as organizations, and associate judges with the courts:<br>
<span class="lb lb-office">Corcubión (Corunna) no. 1</span> has two judges: <span class="lb lb-user">Baltasar Garzón</span>, <span class="lb lb-user">Annise Parker</span>.</p>

<p>For an action (decisions), it becomes possible to specify the court that issued it and the corresponding judge. This will allow to generate statics: which types of decisions a court issues ("positive", "negative") and who was the actual judge. i.e. you'll have detailed statistics per judge.
</p>

<p>Next screenshot illustrates two fields: 1. court 2. judge. First you select the court, CaseBox then automatically populates the "Court" list with the contacts associated with the selected Court.
</p>

<div class="scr">
<img alt="" src="/i/docs/f4_2-contacts-dependant-field-txt.png" class="nb" />
<p>Figure 4.2. Two connected fields linked to Contacts Database.</p>
</div>


<h4><a name="contactdb-object"></a>4.2. Adding contacts to a case as objects</h4>
<p>As explained in <a href="#object-contact">section 3.3.1</a>, we first add applicants/perpetrators to the Contacts database, and later link the contact with the case by specifying it's role and filling in a template. Let's see how it works:</p>

<ul>
<li>First we create a contact (in the screenshot above, fig. 4.1 an organization is being edited, the template of an applicant looks similar).</li>
<li>Once we have the applicant, we need to link it to our case. But it's more than just a connection between the contact and the case: we may define a template for this.</li>
</ul>

<p>
The figure below shows how to add an Applicant to the case (the dropdown menu at the left), the fields of the template, how to select a contact from the database and how to view/edit the general template of the contact (i.e phone number, email etc).
</p>


<div class="scr">
<img alt="" src="/i/docs/f4_3-contact-as-object-txt.png" class="nb" />
<p>Figure 4.3. Adding an Applicant to the case and editing the template.</p>
</div>

-->

<br /><br />
<h3><a name="users"></a>5. Thesaurus</h3>
<p>
Sorting alphabetically: the active language is taken into account (if you select another language and sort, it will use that language.
</p>


<p>
The thesauri is used to manage the list of offices, case types, tags for all types of CaseBox items: cases, actions, objects, files as well as client and organization tags. Only the administrator is able to edit the thesauri. All these are available in the "System Tags" folder within the Thesauri.
</p>

<p>
The thesauri also contains the values for the custom fields. As seen in the picture below, the "Fields" folder has Country, ECHR Keywords. These lists are used in Templates.
</p>

<div class="scr">
<img alt="Case" src="/i/docs/thesauri.png" />
<p>Figure NN. Thesaurus.</p>
</div>

<p>The panel on the right side is the actual place used to setup offices, tags etc.
You should drag&drop those thesauri values or group of values(folders) from the left to the corresponding folder with a red stripe on the right panel. Example: The Offices folder from the left was added to the "Offices" folder on the right.
</p>

<p>The screenshot below illustrates the other approach: drag&dropping specific thesauri items.</p>
<div class="scr">
<img alt="Case" src="/i/docs/thesauri-offices.png" />
<p>Figure NN. Drag&drop entire thesauri folders or distinct items.</p>
</div>


<p>The option to drag&drop thesauri items or entire folders is for you convenience: instead of adding a new thesauri value and then drag&droping it to the right, you first specify a folder in the right panel, and then just add values to that folder on the left. </p>
<br /><br />




<!--
<h3><a name="tags"></a>5. Tags <span class="label label-important">todo</span></h3>
<p>tags & actions ...</p>

-->






<br /><br />
<h3><a name="tmpl"></a>6. Templates and Fields <span class="label label-important">todo</span></h3>

<p>Template structure table definition:</p>

<p>Field definition</p>
<table class="table table-bordered table-condensed" style="width: 100%">
<thead>
<tr>
<th>Field</th>
<th>Info</th>
</tr>
</thead>
<tbody>
<tr>
<td>id</td>
<td>a unique identificator of the field</td>
</tr>
<tr>
<td>pid</td>
<td>parent id: the ID of the parent field.<br /> Use a parent field to control the visibility and values of a child field (i.e. dependent fields)</td>
</tr>

<tr>
<td>template_id</td>
<td>The template this field belongs to (see templates table)</td>
</tr>

<tr>
<td>tag</td>
<td>it's actualy the field type: 
<ul>
<li><b>f</b>: normal field</li>
<li><b>H</b>: a header to visually delimit fields</li>
<li><b>G</b>: a group of fields, use it when you need to control the visibility of several fields at a time.</li>
</ul>
</td>
</tr>

<tr>
<td>level <span class="label label-important">do we need this field ?</span></td>
<td>indentation level, mostly used in field groups to visually indent the field. </td>
</tr>

<tr>
<td>name</td>
<td>field name, used in search queries, website templates. There are special field names like "_title": used by CB to display the node in the grid/tree</td>
</tr>

<tr>
<td>l1 ... lN</td>
<td>field titles in different languages. (languages are configured in `languages` table)</td>
</tr>

<tr>
<td>type</td>
<td>
<ul>
<li><b>varchar</b>: text field</li>
<li><b>date</b>: calendar</li>
<li><b>time</b>: time</li>
<li><b>int</b>: integer</li>
<li><b>float</b>: float</li>
<li><b>combo</b>: a dropdown combobox, conected to a thesauri (also specify thesauri_id field) <span class="label label-important">obsolete: Use _objects field</span>.</li>

<li><b>text</b>: plaing text editor (as a separate tabsheet)</li>
<li><b>html</b>: WYSIWYG Editor similar to gmail (as a separate tabsheet)</li>
<li><b>memo</b>: an inline text field with a specified height in cfg</li>

<li><b>_objects:</b> a complex field type that can show thesauri or tree nodes</li>

<li><b>_sex</b>: used in contact and user profiles. CaseBox then will use a different icon to represent the user.</li>
<li><b>_short_date_format</b>: date formats available in system</li>

<li><b>_language</b>: list of languages available for the core</li>
</ul>
</td>
</tr>

<tr>
<td>order</td>
<td>the ordering(position) of the field in the grid</td>
</tr>


<tr>
<td>cfg</td>
<td>
Field config is specific per field type, here are the options common to all field types:

<ul>
<li><b>readonly</b>: a readonly field</li>
<li><b>maxInstances</b>: [1..n] // how many instances of the field are allowed (by default 1). 
    A multiple field will feature a small [+] icon on the right side that can be clicked to create a new field.</li>
<li><b>multiValued</b>: (true, false)</li>
<li><b>showIn</b>: (grid, top, tabsheet). Specify where to render the field, in the top part, in a separate tabsheet (for a memo field) or in the grid.</li>
<li><b>value</b>: a default value for the field</li>
<li><b>dependancy</b>: a config object that specifies how the current field depends on the parent one (the parent is specified in `pid` column).
               <p>In order to make dependable fields (for example to have two fields, Country/City),
                you need to explicitly specify a `dependancy: {}` config group, even if there are no more dependancy conditions.</p>            
</li>
</ul></td>
</tr>

<tr>
<td>use_as_tags</td>
<td>Value/values of the field will be copied to the `sys_tags` SOLR column, and used to build filter facets.</td>
</tr>


<tr>
<td>solr_column_name</td>
<td>?</td>
</tr>

<tr>
<td>solr_faceted</td>
<td>?</td>
</tr>

</tbody>
</table>


<h3>Field dependancy</h3>
To make dependant fields:
<ul>
  <li>specify `pid`: this will tell CB which is the parent field</li>
  <li>add `dependency` block in `cfg`:
      <pre>"dependency": {
    "pidValues": []   // an ary of values
    ,"tags": []
    ,"tagMode": (OR, AND*)  // AND is default
}</pre>
  </li>
</ul>


<p>Examples:</p>
<table class="table table-bordered table-condensed" style="width: 100%">
<thead>
<tr>
<th>id</th>
<th>pid</th>
<th>tag</th>
<th>name</th>
<th>l1</th>
<th>type</th>
<th>order</th>
<th>cfg</th>
</tr>
</thead>
<tbody>
  <tr>
    <td>10</td>
    <td>NULL</td>
    <td>f</td>
    <td>country</td>
    <td>Country</td>
    <td>_objects</td>
    <td>1</td>
    <td>{"source": "thesauri", "thesauriId": "217"}</td>
  </tr>
  <tr>
    <td>11</td>
    <td>10</td>
    <td>f</td>
    <td>city</td>
    <td>City</td>
    <td>_objects</td>
    <td>2</td>
    <td>{"source": "thesauri", "thesauriId": "variable", "dependency": {} }</td>
  </tr>
</tbody>  
</table>


<h3>_objects Field type</h3>
<p>By object we mean a thesauri item, a tree node, a case, a folder, a system user etc.</p>

<table class="table table-bordered table-condensed" style="width: 100%">
  <tbody>
    <tr>
      <td>source</td>
      <td>
        from where to get values for the editor:
        <ul>
        <li>thesauri: also specify `thesauriId` in cfg</li>
        <li>tree: use the Tree</li>
        <li>related: use related nodes of a given node. <span class="label label-important">to be implemented</span></li>
        <li>field: used for dependent fields when the parentField has a [field] of type _objects.</li>
        <li>custom: a custom source, also specify `url` or `fn` cfg.</li>
      </ul>
       </td>
    </tr>
    <tr>
      <td>scope</td>
      <td><ul>
        <li>tree*: the whole tree</li>
        <li>project: start fetching from the parent Project (going up folder by folder up to a Project)</li>
        <li>parent: parent node</li>
        <li>self: the node itself</li>
        <li>$node_id: a specific tree node (an integer)</li>
        <li>[$node_id_ary]: an array of tree nodes</li>
      </ul></td>
    </tr>
    <tr>
      <td>field</td>
      <td>fieldname of the object in the parentField from which to fetch values if source=field
        <span class="label label-important">I suggest rename to `sourceField`</span></td>
    </tr>
    <tr>
      <td>descendants</td>
      <td>load recursively all children of the node specified in `scope`</td>
    </tr>    
    <tr>
      <td>autoLoad</td>
      <td>fetches the values when the editor is shown</td>
    </tr>
    <tr>
      <td>showDate</td>
      <td>[field_name]. Specify the field name that has a date. <span class="label label-important">this should be more flexible, for ex: the ability to have several columns in form editor</span></td>
    </tr>
    <tr>
      <td>editor</td>
      <td><ul>
        <li>combo: a dropdown list</li>
        <li>form: a popup</li>
      </ul></td>
    </tr>
    <tr>
      <td>renderer</td>
      <td><ul>
        <li>string: selected values are displayed as a comma separated list</li>
        <li>listGreenIcons: values are displayed with a green tick icon</li>
        <li>listObjIcons: use node icons</li>
      </ul></td>
    </tr>
  </tbody>
</table>


<p><b>Examples:</b></p>
<p>Assume we have list of organizations. 
An organization can be a court, and for such organization type we need to specify a list of judges working in the court.
Next: an organization publishes a decision. The decision will have an _objects field `court`. After selecting the court, 
a new field should appear to select the judge working in that court that made the decision.
</p>

<p>Organization template</p>
<table class="table table-bordered table-condensed" style="width: 100%">
<thead>
  <th>id</th>
  <th>pid</th>
  <th>tag</th>
  <th>name</th>
  <th>type</th>
  <th>cfg</th>
  <th>use_as_tags</th>
</thead>
  <tbody>
    <tr>
      <td>1</td>
      <td>NULL</td>
      <td>f</td>
      <td>org_type</td>
      <td>_objects</td>
      <td>{"source": "thesauri", "thesauriId": "17", "editor": "combo"}</td>
      <td>true<br>
          <span class="gr">the value of this field goes to SOLR</span>
      </td>
    </tr>
    <tr>
      <td>2</td>
      <td>1</td>
      <td>f</td>
      <td>judges</td>
      <td>_objects</td>
      <td>
{"source": "tree"<br>
,"scope": [2801]   <span class="gr pl50">// the judges are stored in a folder with ID=2801</span><br>
,"descendants": true         <span class="gr pl50">// fetch all elements of the 2801 folder and all subfolders</span><br>
,"templates": [69]           <span class="gr pl50">// judge templateId=69 (the 2801 folder may contain objects of other types)</span><br>
,"autoLoad": true            <span class="gr pl50">// loading the list of judges when editor is shown</span> <br>
,"renderer": "listObjIcons"  <span class="gr pl50">// visually display a list of nodes with the icon specified in template config</span><br>
,"maxInstances": 3           <span class="gr pl50">// how many judge rows in the grid</span><br>
,"editor":"form"             <span class="gr pl50">// shows a form with a list of judges</span><br>
,"dependency": {<br>
  "pidValues": [634]         <span class="gr pl50">// it means the `judges` field will appear only if the `org_type` field has value '634'</span><br>
}<br>
}</pre></td>
      <td>false</td>
    </tr>
  </tbody>
</table>


<p>Decision template:</p>

<table class="table table-bordered table-condensed" style="width: 100%">
<thead>
  <th>id</th>
  <th>pid</th>
  <th>tag</th>
  <th>name</th>
  <th>type</th>
  <th>cfg</th>
</thead>
  <tbody>
    <tr>
      <td>20</td>
      <td>NULL</td>
      <td>f</td>
      <td>court</td>
      <td>_objects</td>
      <td>{<br>
"source": "tree"<br>
,"editor": "combo"<br>
,"tags": [634]  <span class="gr pl50">// shows only organizations of type `court`</span>
}</td>
    </tr>
    <tr>
      <td>21</td>
      <td>1</td>
      <td>f</td>
      <td>judge</td>
      <td>_objects</td>
      <td>
{"source": "field"<br>
,"field": "judge"            <span class="gr pl50">// the field of the parent node from which to load items</span><br>
,"templates": [69]           <span class="gr pl50">// selects only judges(templateId=69) from the Court.Judges field</span><br>
,"autoLoad": true            <span class="gr pl50">// loading the list of judges when editor is shown</span> <br>
,"renderer": "listObjIcons"  <span class="gr pl50">// visually display a list of nodes with the icon specified in template config</span><br>
,"maxInstances": 3           <span class="gr pl50">// how many judge rows in the grid</span><br>
,"editor":"combo"            <span class="gr pl50">// shows a drop down list of judges</span><br>
,"dependency": {}            <span class="gr pl50">// </span><br>
}<br>
}</pre></td>
    </tr>
  </tbody>
</table>


<h3>Field examples</h3> 
thesauri_id: for combo fields, specify which is the thesauri to display</li>



<br /><br />
<h3><a name="langs"></a>7. Languages <span class="label label-important">todo</span></h3>
<p>
per core, table languages, specify:
short_date_format: 

the grid will render with short_date_format + time_format;
</p>


<br /><br />
<h3><a name="langs"></a>8. Email integration <span class="label label-important">todo</span></h3>
<p>
An email box can be monitored and incoming emails processed. 
CaseBox will accept emails only from registered users (i.e. if the From email matches the email of a user).

The subject of the email should contain the path where to create the action.
</p>
<pre>
Subject: Fwd: A letter here <span style="color: maroon">(/home/0809-Ernazarov)</span>
</pre>
<p> CaseBox will remove 'Fwd: ' from the subject, as well as the path at the end.</p>
<p>There is a system template: template.type = 8, that is used by CaseBox as email.
</p>




<br /><br />
<h3><a name=""></a>Tasks <span class="label label-important">todo</span></h3>
<p>
Task category thesauri is specified in <span class="sys">core.config</span> table in <span class="sys">task_categories</span> variable.
</p>


<br /><br />
<h3><a name=""></a>Templates <span class="label label-important">todo</span></h3>
<p>
The dropdown with icons in Template Editor is in <span class="sys">core.config</span> table in <span class="sys">templateIcons</span> variable.
</p>



<br /><br />
<h3><a name=""></a>File preview <span class="label label-important">todo</span></h3>
<pre>
rtf 
doc
xls
csv
ppt
docx
xlsx
pptx
odt

htm
html
dhtml
xhtml

txt
css
js
json
php
</pre>


<hr class="soften">
<?php include('../footer.php'); ?>








