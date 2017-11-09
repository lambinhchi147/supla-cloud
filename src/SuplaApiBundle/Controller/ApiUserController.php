<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ScheduleAction;
use SuplaBundle\Supla\ServerList;

class ApiUserController extends FOSRestController {

    private $serverList;

    public function __construct(ServerList $serverList) {
        $this->serverList = $serverList;
    }

    /**
     * @api {get} /users/current Current user
     * @apiDescription Get the currently authenticated user
     * @apiGroup Users
     * @apiVersion 2.2.0
     * @apiSuccess {Number} id User ID
     * @apiSuccess {String} email User email address
     * @apiSuccess {Boolean} ioDevicesRegistrationEnabled Whether the registration of new IO Devices is enabled or not.
     * @apiSuccess {Boolean} clientsRegistrationEnabled Whether the registration of new clients is enabled or not.
     */
    public function currentUserAction() {
        return $this->view($this->getUser(), 200);
    }

    /**
     * @Rest\Get("users/current/schedulable-channels")
     */
    public function getUserSchedulableChannelsAction() {
        $ioDeviceManager = $this->get('iodevice_manager');
        $schedulableChannels = $this->get('schedule_manager')->getSchedulableChannels($this->getUser());
        $channelToFunctionsMap = [];
        foreach ($schedulableChannels as $channel) {
            $channelToFunctionsMap[$channel->getId()] = $ioDeviceManager->functionActionMap()[$channel->getFunction()];
        }
        return $this->view([
            'userChannels' => array_map(function (IODeviceChannel $channel) use ($ioDeviceManager) {
                return [
                    'id' => $channel->getId(),
                    'function' => $channel->getFunction(),
                    'functionName' => $ioDeviceManager->channelFunctionToString($channel->getFunction()),
                    'type' => $channel->getType(),
                    'caption' => $channel->getCaption(),
                    'device' => [
                        'id' => $channel->getIoDevice()->getId(),
                        'name' => $channel->getIoDevice()->getName(),
                        'location' => [
                            'id' => $channel->getIoDevice()->getLocation()->getId(),
                            'caption' => $channel->getIoDevice()->getLocation()->getCaption(),
                        ],
                    ],
                ];
            }, $schedulableChannels),
            'actionCaptions' => ScheduleAction::captions(),
            'channelFunctionMap' => $channelToFunctionsMap,
        ]);
    }
}