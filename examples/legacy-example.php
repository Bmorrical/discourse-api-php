<?php

require_once 'src/DiscourseAPI.php'; // include the legacy library

const API_KEY = '43ec8f7a2cad6jldhksno8s0ndd899yhiu2ni3aaefb515d425d85'; // generate your api key from discourse admin
const API_HOST = 'discourse.example.com'; // your discourse installation url (don't include http/https)

$api = new DiscourseAPI('apitest.discoursehosting.net', API_KEY, API_HOST);

// USER EXAMPLE:
    // create user
        $r = $api->createUser('John Doe', 'johndoe', 'johndoe@discoursehosting.com', 'foobar!!');
        print_r($r);

    // in order to activate we need the id
        $r = $api->getUserByUsername('johndoe');
        print_r($r);

    // activate the user
        $r = $api->activateUser($r->apiresult->user->id);
        print_r($r);

    // create a category
        $r = $api->createCategory('a new category', 'cc2222');
        print_r($r);

// CATEGORY EXAMPLE
    // get category id (from user request or make new request)
        $catId = $r->apiresult->category->id;

    // create a topic
        $r = $api->createTopic(
            'This is the title of a brand new topic',
            "This is the body text of a brand new topic. I really don't know what to say",
            $catId,
            'johndoe'
        );
        print_r($r);

        $topicId = $r->apiresult->id;

        $r = $api->createPost(
            'This is the body of a new post in an existing topic',
            $topicId,
            $catId,
            'johndoe'
        );

// CHANGE SITE SETTING
$r = $api->changeSiteSetting('invite_expiry_days', 29);
print_r($r);


