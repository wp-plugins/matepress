<?php

class eWordpressAPI {

    /* ---------------------------------------------------------------------
    // -- http://www.tellinya.com/art2/174/ -- //
    Copyright 2007 5ubliminal@tellinya.com. All rights reserved!
    This must be freely distributed without altering this copyright message.
    The author takes no reponsabilities bla bla bla ...
    Regards.
    // -- This has been coded for Wordpress 2.3 / tested on WP 2.5 -- //
    --------------------------------------------------------------------- */
    var $blogAuthUser;
    var $blogAuthPass;
    var $blogApiID;
    var    $blogRpcServer;
    function __construct($rpc, $user, $pass)
    /* ---------------------------------------------------------------
    For cunstruction use your username, password and URL of the XMLRPC
    server : http://www.blog.com/xmlrpc.php . Check out tutorial on page
    http://www.tellinya.com/art2/173/ for an autodiscovery function.
    --------------------------------------------------------------- */
    {
        $this->blogAuthUser        = $user;
        $this->blogAuthPass        = $pass;
        $this->blogApiID        = "";
        $this->blogRpcServer    = $rpc;
    }

    function eWordpressAPI($rpc, $user, $pass)
    /* Dummy constructor for older PHP (before 5) */
    {
        $this->_construct($rpc, $user, $pass);
    }

    function doRpcRequest($url,$method,$params,$returnbool=0)
    /* ---------------------------------------------------------------
    Internal function that sends the request to the XMLRPC server.
    To use a different XMLRPC implementation that the PHP one this
    is where you have to play around!
    --------------------------------------------------------------- */
    {
        $request = xmlrpc_encode_request($method, $params);
        $context = stream_context_create(
            array(
                'http' => array(
                    'method' => "POST",
                    'header' => "Content-Type: text/xml",
                    'content' => $request
                )
            )
        );

        $file = file_get_contents($url, false, $context);
        $response = xmlrpc_decode($file);
        if($returnbool)
            return (xmlrpc_is_fault($response) ? false : true);
        //return array($response['faultString'],$response['faultCode']);
        return $response;
    }

    function xml2Post($postXml)
    /* ---------------------------------------------------------------
    Internal function that convert XML representation of a Wordpress
    post to a PHP array.
    --------------------------------------------------------------- */
    {
        if($postXml['dateCreated']->timestamp)
            $postXml['dateCreated']=$postXml['dateCreated']->timestamp;
        if($postXml['date_created_gmt']->timestamp)
            $postXml['dateCreatedGmt']=$postXml['date_created_gmt']->timestamp;
        unset($postXml['date_created_gmt']);
        if(isset($postXml['content'])){
            $postXml['content'] = str_replace("<category></category>","",$postXml['content']);
            if(preg_match(
                "/^<title>(.*?)<\/title>".
                "<category>(.*)<\/category>(.*)$/Ui"
                ,$postXml['content'],$pcs)
            ){
                $postXml['content']=$pcs[3];
                $postXml['title']=$pcs[1];
                $postXml['category']=explode(",",$pcs[2]);
            }
        }
        return $postXml;
    }

    function xml2Page($postXml)
    /* ---------------------------------------------------------------
    Internal function that convert XML representation of a Wordpress
    pager (like the About page) to a PHP array.
    --------------------------------------------------------------- */
    {
        if($postXml['dateCreated']->timestamp)
            $postXml['dateCreated']=$postXml['dateCreated']->timestamp;
        if($postXml['date_created_gmt']->timestamp)
            $postXml['dateCreatedGmt']=$postXml['date_created_gmt']->timestamp;
        unset($postXml['date_created_gmt']);

        return $postXml;
    }

    function post2Xml($title, $text, $categories)
    /* ---------------------------------------------------------------
    Internal function that converts a PHP array to XML representation
    of a Wordpress post.
    --------------------------------------------------------------- */
    {
        if(is_string($categories)) $categories=preg_split("/[^0-9a-z]+/i",$categories);
        if(!count($categories)) array_push($categories,1);
        $categories=implode(",",$categories);
        $postXml =
            "<title>".html_entity_decode($title)."</title>".
            "<category>".$categories."</category>".
            html_entity_decode($text)."";

        return $postXml;
    }

    function getPost($postID,$mtFull=false)
    /* ---------------------------------------------------------------
    This function will retrieve a post from the blog. You need the POST
    ID and the options $mtFull asks the script to use MovableType API
    for extended informations compared to the Blogger API short version.
    --------------------------------------------------------------- */
    // - Blogger Type API Function -
    {
        if(is_array($postID)) return $this->getPosts($postID,$mtFull);
        $params = array();
        if(!$mtFull) array_push($params, (string)$this->blogApiID);
        array_push($params, (string)$postID);
        array_push($params, (string)$this->blogAuthUser);
        array_push($params, (string)$this->blogAuthPass);
        $resp = $this->doRpcRequest($this->blogRpcServer,
            ($mtFull ? "metaWeblog.getPost" : "blogger.getPost"),
            $params
        );
        if((int)$resp['faultCode']) return $resp;
        return $this->xml2Post($resp);
    }

    function getPosts($postIDs,$mtFull=false)
    /* ---------------------------------------------------------------
    Helper function that does exactly as the above but takes an array
    of IDs. Any of these 2 functions can be used for single integers or
    arrays as they will call each other as needed.
    --------------------------------------------------------------- */
    // - Blogger Type API Function -
    {
        if(!is_array($postIDs)) return $this->getPost($postIDs,$mtFull);
        $posts = array();
        foreach($postIDs as $postID){
            $post=$this->getPost($postID,$mtFull);
            if((int)$post['faultCode']) continue;
            $posts[$postID]=$post;
        }
        return $posts;
    }

    function deletePost($postID)
    /* ---------------------------------------------------------------
    This function will delete a POST by its ID.
    --------------------------------------------------------------- */
    // - Blogger Type API Function -
    {
        if(is_array($postID)) return $this->deletePosts($postID);
        $params = array();
        array_push($params, (string)$this->blogApiID);
        array_push($params, (string)$postID);
        array_push($params, (string)$this->blogAuthUser);
        array_push($params, (string)$this->blogAuthPass);
        array_push($params, (bool)false);
        $resp = $this->doRpcRequest($this->blogRpcServer, "blogger.deletePost", $params);

        if((int)$resp['faultCode']) return $resp;
        return $resp;
    }

    function deletePosts($postIDs)
    /* ---------------------------------------------------------------
    Helper function that does exactly as the above but takes an array
    of IDs. Any of these 2 functions can be used for single integers or
    arrays as they will call each other as needed.
    --------------------------------------------------------------- */
    // - Blogger Type API Function -
    {
        if(!is_array($postIDs)) return $this->deletePost($postIDs);
        foreach($postIDs as $key=>$postID){
            if($this->deletePost($postID,$mtFull) === 1){
                unset($postIDs[$key]);

                continue;

            }

        }

        return (count($postIDs) ? $postIDs : true);

    }

    function getUsers()

    /* ---------------------------------------------------------------

    This function will list blog users.

    --------------------------------------------------------------- */

    // - Blogger Type API Function -

    {

        $params = array();

        array_push($params, (string)$this->blogApiID);

        array_push($params, (string)$this->blogAuthUser);

        array_push($params, (string)$this->blogAuthPass);

        $resp = $this->doRpcRequest($this->blogRpcServer, "blogger.getUsersBlogs", $params);

        if((int)$resp['faultCode']) return $resp;

           return $resp;

    }

    function getUserInfo()
    /* ---------------------------------------------------------------
    This function will list informations about the current user you use
    to access the API.
    --------------------------------------------------------------- */
    // - Blogger Type API Function -
    {
        $params = array();
        array_push($params, (string)$this->blogApiID);
        array_push($params, (string)$this->blogAuthUser);
        array_push($params, (string)$this->blogAuthPass);
        $resp = $this->doRpcRequest($this->blogRpcServer, "blogger.getUserInfo", $params);

        if((int)$resp['faultCode']) return $resp;

           return $resp;
    }

    function newPost($postTitle, $postText, $postCategories, $publish=true)
    /* ---------------------------------------------------------------
    This function will post a new topic. Paramaters are selfexplanatory.
    If $publish is true the post is published immediately otherwise
    it's placed inside drafts. $postCategories is a numeric array or
    a string with category IDs separated by ,
    --------------------------------------------------------------- */
    // - Blogger Type API Function -
    {
        $postXml=$this->post2Xml($postTitle, $postText, $postCategories);
        if($postXml === false) return false;
        $params = array();
        array_push($params, (string)$this->blogApiID);
        array_push($params, (string)$blogID);
        array_push($params, (string)$this->blogAuthUser);
        array_push($params, (string)$this->blogAuthPass);
        array_push($params, (string)$postXml);
        array_push($params, (bool)$publish);
        $resp = $this->doRpcRequest($this->blogRpcServer, "blogger.newPost", $params);
        if((int)$resp['faultCode']) return $resp;
           return $resp;
    }

    function newPostMetaWeblog($postTitle, $postText, $tags, $publish=true, $moreText=null,
        $allowPings=true, $allowComments=true, $convertLineBreaks = true)
    /* ---------------------------------------------------------------
    This function will post a new topic with tags.
    Compatible eWordpressAPI function provided by Maximus Marius @ http://seo4you.pl/
    ... with a few gentle tweaks from me :)
    - $postTitle, $postText - are what they are named
    - $tags can be an array or a CSV string
    - $moreText is optional body text to show up when you read the whole post
    - $allowPing, $allowComments, $convertLineBreaks are what they claim to be
    You can gracefully degrade from newPost to this function.

    Cathegories will act as Tags.

    --------------------------------------------------------------- */

    // - MetaWeblog Type API Function -
    {
        $params    = array();
        array_push($params, (string)$this->blogApiID);
        array_push($params, (string)$this->blogAuthUser);
        array_push($params, (string)$this->blogAuthPass);
        //-- We config new post here in MW format
        $newPostFields            = array();
        //-- Just in case we get an array here

        if(is_array($tags)) $tags = implode(",",$tags);
        //-- Users may use ,;. to separate tags
        $tags = preg_replace("/[,;\.]/s",",",$tags);
        $newPostFields['post_type']    = 'post';
        $newPostFields['title']        = $postTitle;

        //-- If not string - not good
        if(is_string($tags) && strlen($tags))
            $newPostFields['mt_keywords']    = $tags;
        $newPostFields['description']    = $postText;

        //-- If not string - not good
        if(is_string($moreText) && strlen($moreText))
            $newPostFields['mt_text_more']    = $moreText;
        $newPostFields['mt_allow_comments']    = (int)$allowComments;
        $newPostFields['mt_allow_pings']    = (int)$allowPings;
        $newPostFields['mt_convert_breaks']    = (int)$convertLineBreaks;

        //-- Let's ship it over
        array_push($params, $newPostFields);
        array_push($params, (bool)$publish);
        $resp    = $this->doRpcRequest($this->blogRpcServer, "metaWeblog.newPost", $params);
        if((int)$resp['faultCode']) return $resp;
        return $resp;
    }

    function newPostTags($postTitle, $postText, $tags, $publish=true)
    /* Shorter version of the above function */
    {
        return $this->newPostMetaWeblog($postTitle, $postText, $tags, $publish);
    }

    function editPost($postID, $postTitle, $postText, $postCategories, $publish=true)
    /* ---------------------------------------------------------------
    This function will edit an existing post. Paramaters are selfexplanatory.
    If $publish is true the post is published immediately otherwise
    it's placed inside drafts. $postCategories is a numeric array or
    a string with category IDs separated by ,
    --------------------------------------------------------------- */
    // - Blogger Type API Function -
    {
        $postXml=$this->post2Xml($postTitle, $postText, $postCategories);
        if($postXml === false) return false;
        $params = array();
        array_push($params, (string)$this->blogApiID);
        array_push($params, (string)$postID);
        array_push($params, (string)$this->blogAuthUser);
        array_push($params, (string)$this->blogAuthPass);
        $postXml=$this->post2Xml($postTitle, $postText, $postCategories);
        array_push($params, (string)$postXml);
        array_push($params, (bool)$publish);
        $resp = $this->doRpcRequest($this->blogRpcServer, "blogger.editPost", $params);
        if((int)$resp['faultCode']) return $resp;
        return $resp;

    }

    function getRecentPosts($numPosts, $blogID=0)
    /* ---------------------------------------------------------------
    This function will get the latest POSTS including title, description
    and categories. I advise against using it. Use the getRecentPostIDs
    instead and then just query each ID to get post info.
    --------------------------------------------------------------- */
    // - Blogger Type API Function -
    {
        $params = array();
        array_push($params, (string)$this->blogApiID);
        array_push($params, (string)$blogID);
        array_push($params, (string)$this->blogAuthUser);
        array_push($params, (string)$this->blogAuthPass);
        array_push($params, (int)$numPosts);
        $resp = $this->doRpcRequest($this->blogRpcServer, "blogger.getRecentPosts", $params);
        if((int)$resp['faultCode']) return $resp;
        foreach($resp as $key=>$val){
            $resp[$key] = $this->xml2Post($val);
        }
           return $resp;
    }

    function getRecentPostIDs($numPosts, $blogID=0)
    /* ---------------------------------------------------------------
    This function will get the latest POSTS IDs so you can query each
    of them to get post info useing getPost().
    --------------------------------------------------------------- */
    // - Blogger Type API Function -
    {
        $resp = $this->getRecentPosts($numPosts, $blogID);
        if((int)$resp['faultCode']) return false;
        $ids = array();
        foreach($resp as $key=>$val){
            array_push($ids,$val['postid']);
        }
           return $ids;
    }

    function getAllPosts($blogID=0)
    /* ---------------------------------------------------------------
    This function is a helper function. The getRecentPosts with $numPosts
    set to 0 will return the same thing. All the POSTS.
    --------------------------------------------------------------- */
    // - Blogger Type API Function -
    {
        return $this->getRecentPosts(0,$blogID);
    }

    function getAllPostIDs($blogID=0)
    /* ---------------------------------------------------------------
    This function is a helper function. The getRecentPostIDs with $numPosts
    set to 0 will return the same thing. All the POSTS IDs.
    --------------------------------------------------------------- */
    // - Blogger Type API Function -
    {
        return $this->getRecentPostIDs(0,$blogID);
    }

    function getPage($pageID,$blogID=0)
    /* ---------------------------------------------------------------
    This function will get a page by ID. A page is not a post. It's
    like the About page. You know better.
    --------------------------------------------------------------- */
    // - WordPress Type API Function -
    {
        $params = array();
        array_push($params, (string)$blogID);
        array_push($params, (string)$pageID);
        array_push($params, (string)$this->blogAuthUser);
        array_push($params, (string)$this->blogAuthPass);
        $resp = $this->doRpcRequest($this->blogRpcServer, "wp.getPage", $params);
        if((int)$resp['faultCode']) return $resp;
        return $this->xml2Page($resp);
    }

    function deletePage($pageID,$blogID=0)
    /* ---------------------------------------------------------------
    This function will delete a page. It returns an array of undeleted
    pages or true.
    --------------------------------------------------------------- */
    // - WordPress Type API Function -
    {
        if(is_array($pageID)) return $this->deletePages($pageID,$blogID);
        $params = array();
        array_push($params, (string)$blogID);
        array_push($params, (string)$this->blogAuthUser);
        array_push($params, (string)$this->blogAuthPass);
        array_push($params, (string)$pageID);
        $resp = $this->doRpcRequest($this->blogRpcServer, "wp.deletePage", $params);
        if((int)$resp['faultCode']) return $resp;
        return $resp;
    }

    function deletePages($pageIDs,$blogID=0)
    /* ---------------------------------------------------------------
    Helper function that does exactly as the above but takes an array
    of IDs. Any of these 2 functions can be used for single integers or
    arrays as they will call each other as needed. It returns an array
    of undeleted pages or true.
    --------------------------------------------------------------- */
    // - WordPress Type API Function -
    {
        if(!is_array($pageIDs)) return $this->deletePage($pageIDs,$blogID);
        foreach($pageIDs as $key=>$pageID){
            if($this->deletePage($pageID,$mtFull) === 1){
                unset($pageIDs[$key]);
                continue;
            }
        }
        return (count($pageIDs) ? $pageIDs : true);
    }

    function listPages($blogID=0)
    /* ---------------------------------------------------------------
    This function will list pages with short info about them.
    --------------------------------------------------------------- */
    // - WordPress Type API Function -
    {
        $params = array();
        array_push($params, (string)$blogID);
        array_push($params, (string)$this->blogAuthUser);
        array_push($params, (string)$this->blogAuthPass);
        $resp = $this->doRpcRequest($this->blogRpcServer, "wp.getPageList", $params);
        if((int)$resp['faultCode']) return $resp;
        foreach($resp as $key=>$val){
            $resp[$key] = $this->xml2Page($val);
        }
        return $resp;
    }

    function getPages($blogID=0)

    /* ---------------------------------------------------------------

    This function will list pages with longer info about them.

    --------------------------------------------------------------- */

    // - WordPress Type API Function -

    {
        $params = array();
        array_push($params, (string)$blogID);
        array_push($params, (string)$this->blogAuthUser);
        array_push($params, (string)$this->blogAuthPass);
        $resp = $this->doRpcRequest($this->blogRpcServer, "wp.getPages", $params);
        if((int)$resp['faultCode']) return $resp;
        foreach($resp as $key=>$val){
            $resp[$key] = $this->xml2Page($val);
        }
        return $resp;
    }

    function getPageIDs($blogID=0)
    /* ---------------------------------------------------------------
    This function will list only page IDs so you can query further
    informations using getPage().
    --------------------------------------------------------------- */
    // - WordPress Type API Function -
    {
        $resp = $this->getPages($blogID);
        if((int)$resp['faultCode']) return $resp;
        $ids=array();

        foreach($resp as $key=>$val){
            array_push($ids, $val['page_id']);
        }
      return $ids;
    }

    function getCategories($blogID=0)
    /* ---------------------------------------------------------------
    This function will list categories with details using wordPress API
    --------------------------------------------------------------- */
    // - WordPress Type API Function -
    {
        $params = array();
        array_push($params, (string)$blogID);
        array_push($params, (string)$this->blogAuthUser);
        array_push($params, (string)$this->blogAuthPass);
        $resp = $this->doRpcRequest($this->blogRpcServer, "wp.getCategories", $params);
        if((int)$resp['faultCode']) return $resp;
           return $resp;
    }

    function getCategoryIDs($blogID=0)
    /* ---------------------------------------------------------------
    This function will list only category ID and name. It returns an
    array $entry[$categoryID]=$categoryName. Use IDs for new POSTs
    --------------------------------------------------------------- */
    // - WordPress Type API Function -
    {
        $resp = $this->getCategories($blogID);
        if((int)$resp['faultCode']) return $resp;
        foreach($resp as $key=>$val){
            $ids[$val['categoryId']]=$val['categoryName'];
        }
           return $ids;
    }

    function newCategory($categoryName,$blogID=0)
    /* ---------------------------------------------------------------
    This function will create a new category.
    --------------------------------------------------------------- */
    // - WordPress Type API Function -
    {
        $params = array();
        array_push($params, (string)$blogID);
        array_push($params, (string)$this->blogAuthUser);
        array_push($params, (string)$this->blogAuthPass);
        //-- Fix provided by netizen @ http://linkdump.f0wl.org/
        array_push($params, array("name" => $categoryName));
        $resp = $this->doRpcRequest($this->blogRpcServer, "wp.newCategory", $params);
        if((int)$resp['faultCode']) return $resp;
           return $resp;
    }

    function getAuthors($blogID=0)
    /* ---------------------------------------------------------------
    This function will list Authors of Blog.
    --------------------------------------------------------------- */
    // - WordPress Type API Function -
    {
        $params = array();
        array_push($params, (string)$blogID);
        array_push($params, (string)$this->blogAuthUser);
        array_push($params, (string)$this->blogAuthPass);
        $resp = $this->doRpcRequest($this->blogRpcServer, "wp.getAuthors", $params);
        if((int)$resp['faultCode']) return $resp;
        foreach($resp as $key=>$val){
            if(isset($val['meta_value']))
                $val['meta_value']=unserialize($val['meta_value']);
            $resp[$key] = $val;
        }
        return $resp;
    }

    function publishPost($postID)
    /* ---------------------------------------------------------------
    This function will Publish posts from Drafts.
    --------------------------------------------------------------- */
    // - MovableType Type API Function -
    {
        $params = array();
        array_push($params, (string)$postID);
        array_push($params, (string)$this->blogAuthUser);
        array_push($params, (string)$this->blogAuthPass);
        $resp = $this->doRpcRequest($this->blogRpcServer, "mt.publishPost", $params);
        if((int)$resp['faultCode']) return $resp;
           return $resp;
    }
    //------------------------------------------------------

};

?>