<?php

$eventBase = new EventBase();

$event = new Event($eventBase, -1, Event::TIMEOUT | Event::PERSIST, function () {
    var_dump(1);
});

$event->addTimer(1);

$eventBase->loop();