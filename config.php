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


require_once INCLUDE_DIR . 'class.plugin.php';

class ExtMailPluginConfig extends PluginConfig {

    function translate() {
        if (!method_exists('Plugin', 'translate')) {
            return array(
                function ($x) {
                    return $x;
                },
                function ($x, $y, $n) {
                    return $n != 1 ? $y : $x;
                }
            );
        }
        return Plugin::translate('ext-mail');
    }

    function getOptions() {

        list ($__, $_N) = self::translate();

        return array(
            'ext-mail' => new SectionBreakField(array(
                'label' => $__('Extended mail notifications'),
                'hint'  => $__('Extended mail notifications plugin by Anastasia Vasilyeva')
            )),
            'ext-mail-ticket' => new SectionBreakField(array(
                'label' => $__('Ticket Status Change Notification'),
            )),
            'ticket-status-change-subject' => new TextboxField([
                'label'         => $__('Subject'),
                'default'       => $__('Ticket [#%{ticket.number}] is %{ticket.status}'),
                'configuration' => [
                    'size'   => 30,
                    'length' => 200
                ],
            ]),
            'ticket-status-change-body' => new TextareaField([
                'label'         => $__('Body'),
                'default'       => '
                    <h3><strong>Hi %{recipient.name}</strong>,</h3>
                    A ticket, <a href="%{ticket.staff_link}">#%{ticket.number}</a> is
                    %{ticket.status}.
                    <br>
                    <br>
                    <div>
                        To view or respond to the ticket, please 
                        <a href="%{ticket.staff_link}"><span style="color: rgb(84, 141, 212);">login</span></a> 
                        to the support system.
                    </div>
                    <em style="font-size: small">
                        Your friendly 
                        <span style="font-size: smaller">(although with limited patience)</span> 
                        Customer Support System
                    </em>
                    <br>
                    <img src="cid:b56944cb4722cc5cda9d1e23a3ea7fbc" height="19"
                        alt="Powered by osTicket" width="126" style="width: 126px;">',
                'configuration' => [
                    'html' => TRUE,
                ]
            ]),
            'ticket-status-change-owners' => new BooleanField(array(
                'label' => $__('Owner & Collaborators'),
            )),
            'ticket-status-change-admin-mail' => new BooleanField(array(
                'label' => $__('Admin Email'),
            )),
            'ticket-status-change-department-manager' => new BooleanField(array(
                'label' => $__('Department Manager'),
            )),
            'ticket-status-change-department-members' => new BooleanField(array(
                'label' => $__('Department Members'),
            )),
            'ticket-status-change-organization-account-manager' => new BooleanField(array(
                'label' => $__('Organization Account Manager'),
            )),
            'ticket-status-change-assigned' => new BooleanField(array(
                'label' => $__('Assigned Agent / Team'),
            )),

            'ext-mail-task' => new SectionBreakField(array(
                'label' => $__('Task Status Change Notification'),
            )),
            'task-status-change-subject' => new TextboxField([
                'label'         => $__('Subject'),
                'default'       => $__('Task [#%{task.number}] is %{task.status}'),
                'configuration' => [
                    'size'   => 30,
                    'length' => 200
                ],
            ]),
            'task-status-change-body' => new TextareaField([
                'label'         => $__('Body'),
                'default'       => '
                    <h3><strong>Hi %{recipient.name}</strong>,</h3>
                    A task, <a href="%{task.staff_link}">#%{task.number}</a> is
                    %{task.status}.
                    <br>
                    <br>
                    <div>
                        To view or respond to the task, please 
                        <a href="%{task.staff_link}"><span style="color: rgb(84, 141, 212);">login</span></a> 
                        to the support system.
                    </div>
                    <em style="font-size: small">
                        Your friendly 
                        <span style="font-size: smaller">(although with limited patience)</span> 
                        Customer Support System
                    </em>
                    <br>
                    <img src="cid:b56944cb4722cc5cda9d1e23a3ea7fbc" height="19"
                        alt="Powered by osTask" width="126" style="width: 126px;">',
                'configuration' => [
                    'html' => TRUE,
                ]
            ]),
            'task-status-change-admin-mail' => new BooleanField(array(
                'label' => $__('Admin Email'),
            )),
            'task-status-change-department-manager' => new BooleanField(array(
                'label' => $__('Department Manager'),
            )),
            'task-status-change-department-members' => new BooleanField(array(
                'label' => $__('Department Members'),
            )),
            'task-status-change-assigned' => new BooleanField(array(
                'label' => $__('Assigned Agent / Team'),
            )),
        );
    }

}
