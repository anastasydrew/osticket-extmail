<?php
/**
 * Copyright 2020 Anastasia Vasilyeva
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0

 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


require_once(INCLUDE_DIR . 'class.signal.php');
require_once(INCLUDE_DIR . 'class.plugin.php');
require_once(INCLUDE_DIR . 'class.ticket.php');
require_once(INCLUDE_DIR . 'class.task.php');

require_once(INCLUDE_DIR . 'class.osticket.php');
require_once(INCLUDE_DIR . 'class.config.php');
require_once(INCLUDE_DIR . 'class.util.php');
require_once(INCLUDE_DIR . 'class.mailer.php');
require_once('config.php');


class ExtMailPlugin extends Plugin {

    var $config_class = "ExtMailPluginConfig";

    /**
     * The entrypoint of the plugin, keep short, always runs.
     */
    function bootstrap() {
        Signal::connect('model.updated', array($this, 'onModelUpdated'));
    }

    /**
     * Model update signal
     *
     * @param $model
     * @param array $data
     * @return void
     * @throws Exception
     */
    function onModelUpdated($model, $data) {
        switch (get_class($model)) {
            case Ticket::class:
                $this->onTicketUpdated($model, $data);
                break;
            case Task::class:
                $this->onTaskUpdated($model, $data);
                break;
        }
    }

    /**
     * On ticket updated
     * @param Ticket $ticket
     * @param array $data
     */
    function onTicketUpdated($ticket, $data) {
        
        if (array_key_exists('status_id', $data['dirty']) &&
            $data['dirty']['status_id'] != '' &&
            $data['dirty']['status_id'] != $ticket->getStatusId()) {
            $this->onTicketStatusChanged($ticket, $data);
        } elseif (array_key_exists('staff_id', $data['dirty']) ||
            array_key_exists('team_id', $data['dirty'])
        ) {
            $this->onTicketAssigneeChanged($ticket, $data);
        }
        
    }

    /**
     * On ticket status changed
     * @param Ticket $ticket
     * @param array $data
     */
    function onTicketStatusChanged($ticket, $data) {

        $subject = $this->getConfig()->get('ticket-status-change-subject');
        $body = $this->getConfig()->get('ticket-status-change-body');

        if ($this->getConfig()->get('ticket-status-change-owners'))
            $recipients = $ticket->getRecipients();
        else
            $recipients = new MailingList();

        if ($this->getConfig()->get('ticket-status-change-admin-mail')) {
            $admins = Staff::objects()->filter(['isadmin' => 1]);
            foreach ($admins as $admin)
                $recipients->addRecipient($admin);
        }

        if ($this->getConfig()->get('ticket-status-change-department-manager') &&
            ($department_manager = $ticket->getDept()->getManager())
        ) {
            $recipients->addRecipient($department_manager);
        }

        if ($this->getConfig()->get('ticket-status-change-department-members') &&
            ($department_members = $ticket->getDept()->getMembers())
        ) {
            foreach ($department_members as $department_member)
                $recipients->addRecipient($department_member);
        }

        if ($this->getConfig()->get('ticket-status-change-organization-account-manager') &&
            ($org = $ticket->getOwner()->getOrganization()) &&
            ($account_manager = $org->getAccountManager())
        ) {

            if ($account_manager instanceof Team &&
                ($account_manager_members = $account_manager->getMembers())
            )
                foreach ($account_manager_members as $item)
                    $recipients->addRecipient($item);

            elseif ($account_manager instanceof Staff)
                $recipients->addRecipient($account_manager);
        }

        if ($this->getConfig()->get('ticket-status-change-assigned')) {

            if ($assignee = $ticket->getAssignee())
                $recipients->addRecipient($assignee);

            if (($team = $ticket->getTeam()) &&
                ($team_members = $team->getMembers())
            ) {
                foreach ($team_members as $item)
                    $recipients->addRecipient($item);
            }
        }

        $email_list = [];

        foreach ($recipients as $recipient) {

            $email = $recipient->getEmail();

            if (in_array($email, $email_list))
                continue;

            $this->sendEmail($ticket, $recipient, $subject, $body);
            $email_list[] = $email;
        }
    }

    /**
     * On ticket assignee changed
     * @param Ticket $ticket
     * @param array $data
     */
    function onTicketAssigneeChanged($ticket, $data) {

    }
    
    /**
     * On task updated
     * @param Task $task
     * @param array $data
     */
    function onTaskUpdated($task, $data) {

        if (array_key_exists('flags', $data['dirty']) &&
            $data['dirty']['flags'] != $task->flags) {
            $this->onTaskStatusChanged($task, $data);
        } elseif (array_key_exists('staff_id', $data['dirty']) ||
            array_key_exists('team_id', $data['dirty'])) {
            $this->onTaskAssigneeChanged($task, $data);
        }
        
    }
    
    /**
     * On task status changed
     * @param Task $task
     * @param array $data
     */
    function onTaskStatusChanged($task, $data) {

        $subject = $this->getConfig()->get('task-status-change-subject');
        $body = $this->getConfig()->get('task-status-change-body');

        $recipients = new MailingList();

        if ($this->getConfig()->get('task-status-change-admin-mail')) {
            $admins = Staff::objects()->filter(['isadmin' => 1]);
            foreach ($admins as $admin)
                $recipients->addRecipient($admin);
        }

        if ($this->getConfig()->get('task-status-change-department-manager') &&
            ($department_manager = $task->getDept()->getManager())
        ) {
            $recipients->addRecipient($department_manager);
        }

        if ($this->getConfig()->get('task-status-change-department-members') &&
            ($department_members = $task->getDept()->getMembers())
        ) {
            foreach ($department_members as $department_member)
                $recipients->addRecipient($department_member);
        }

        if ($this->getConfig()->get('task-status-change-assigned')) {

            if ($assignee = $task->getAssignee())
                $recipients->addRecipient($assignee);

            if (($team = $task->getTeam()) &&
                ($team_members = $team->getMembers())
            ) {
                foreach ($team_members as $item)
                    $recipients->addRecipient($item);
            }
        }

        $email_list = [];

        foreach ($recipients as $recipient) {

            $email = $recipient->getEmail();

            if (in_array($email, $email_list))
                continue;

            $this->sendEmail($task, $recipient, $subject, $body);
            $email_list[] = $email;
        }
    }

    /**
     * On task assignee changed
     * @param Task $task
     * @param array $data
     */
    function onTaskAssigneeChanged($task, $data) {

    }

    /**
     * @param Ticket|Task $model
     * @param EmailRecipient $recipient
     * @param string $subject
     * @param string $body
     * @param array $extra
     * @global osTicket $ost
     */
    function sendEmail($model, $recipient, $subject, $body, $extra=array()) {

        global $ost;

        if (!$email = $ost->getConfig()->getDefaultEmail())
            return;

        $context = array_merge(
            ['recipient' => $recipient], $extra
        );

        $email->send(
            $recipient,
            $model->replaceVars($subject, $context),
            $model->replaceVars($body, $context)
        );

    }

}
