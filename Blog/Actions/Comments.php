<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Comments extends BlogHTML
{
    /**
     * Recursively displays comments of a given post according to several parameters
     *
     * @access  public
     * @param   int     $id             post id
     * @param   int     $parent         parent comment id
     * @param   int     $level          deep level on thread
     * @param   int     $thread         1 to show full thread
     * @param   int     $reply_link     1 to show reply-to link
     * @param   array   $data           Array with comments data if null it's loaded from model.
     * @return  string XHTML template content
     */
    function ShowComments($id, $fast_url, $parent, $level, $thread, $reply_link, $data = null)
    {
        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('Comment.html');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        if (is_null($data)) {
            $comments = $model->GetComments($id, null);
        } else {
            $comments = $data;
        }

        if (!Jaws_Error::IsError($comments)) {
            $date = $GLOBALS['app']->loadDate();
            foreach ($comments as $c) {
                $tpl->SetBlock('comment');
                $tpl->SetVariable('id', $c['id']);
                $tpl->SetVariable('num', $c['num']);
                $tpl->SetVariable('parent_id', $c['gadget_reference']);
                $tpl->SetVariable('name', $c['name']);
                $email =$c['email'];

                $GLOBALS['app']->Registry->LoadFile('Policy');
                $_obfuscator = $this->GetRegistry('obfuscator', 'Policy');
                if (($_obfuscator != 'DISABLED') && (!empty($email))){
                    require_once JAWS_PATH . 'gadgets/Policy/obfuscators/' . $_obfuscator . '.php';
                    $obf = new $_obfuscator();
                    $tpl->SetVariable('email', $obf->Get($email, _t('GLOBAL_EMAIL')));
                } elseif (empty($email)) {
                    $tpl->SetVariable('email', '');
                } else {
                    $tpl->SetVariable('email', '<a href="mailto:' . $email . '">' . _t('GLOBAL_EMAIL') . '</a>');
                }
                $tpl->SetVariable('url', empty($c['url'])? 'javascript: void();' : $c['url']);
                $tpl->SetVariable('ip_address', '127.0.0.1');
                $tpl->SetVariable('avatar_source', $c['avatar_source']);
                $tpl->SetVariable('title', $c['title']);
                $tpl->SetVariable('replies', $c['replies']);
                $tpl->SetVariable('commentname', 'comment'.$c['id']);
                $commentsText = $this->ParseText($c['msg_txt']);
                $tpl->SetVariable('comments', $commentsText);
                $tpl->SetVariable('createtime-iso',       $c['createtime']);
                $tpl->SetVariable('createtime',           $date->Format($c['createtime']));
                $tpl->SetVariable('createtime-monthname', $date->Format($c['createtime'], 'MN'));
                $tpl->SetVariable('createtime-monthabbr', $date->Format($c['createtime'], 'M'));
                $tpl->SetVariable('createtime-month',     $date->Format($c['createtime'], 'm'));
                $tpl->SetVariable('createtime-dayname',   $date->Format($c['createtime'], 'DN'));
                $tpl->SetVariable('createtime-dayabbr',   $date->Format($c['createtime'], 'D'));
                $tpl->SetVariable('createtime-day',       $date->Format($c['createtime'], 'd'));
                $tpl->SetVariable('createtime-year',      $date->Format($c['createtime'], 'Y'));
                $tpl->SetVariable('createtime-time',      $date->Format($c['createtime'], 'g:ia'));

                if ($c['status'] == 'spam') {
                    $tpl->SetVariable('status_message', _t('BLOG_COMMENT_IS_SPAM'));
                } elseif ($c['status'] == 'waiting') {
                    $tpl->SetVariable('status_message', _t('BLOG_COMMENT_IS_WAITING'));
                } else {
                    $tpl->SetVariable('status_message', '&nbsp;');
                }
                $tpl->SetVariable('level', $level);

                $tpl->SetBlock('comment/reply-link');
                $tpl->SetVariablesArray($c);
                if ($reply_link) {
                    $c['gadget_reference'] = empty($fast_url) ? $c['gadget_reference'] : $fast_url;
                    $tpl->SetVariable('reply-link', '<a href="'.
                                                    $this->GetURLFor('Reply', array('id' => $c['gadget_reference'],
                                                                                    'comment_id' => $c['id'], )).'">'.
                                                    _t('BLOG_REPLY').'</a>');
                } else {
                    $tpl->SetVariable('reply-link', _t('BLOG_REPLY'));
                }
                $tpl->ParseBlock('comment/reply-link');

                if (count($c['childs']) > 0) {
                    $tpl->SetBlock('comment/thread');
                    $tpl->SetVariable(
                        'thread',
                        $this->ShowComments(
                            $id,
                            $fast_url,
                            $c['id'],
                            $level + 1,
                            $thread,
                            $reply_link,
                            $c['childs']
                        )
                    );
                    $tpl->ParseBlock('comment/thread');
                }
                $tpl->ParseBlock('comment');
            }
        }

        return $tpl->Get();
    }

    /**
     * Displays a given blog comment
     *
     * @access  public
     * @param   int $id     comment id
     * @return  string XHTML template content
     */
    function ShowSingleComment($id)
    {
        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('Comment.html');
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        $comment = $model->GetComment($id);
        if (!Jaws_Error::IsError($comment)) {
            $date = $GLOBALS['app']->loadDate();
            $tpl->SetBlock('comment');
            $tpl->SetVariable('id', $comment['id']);
            $tpl->SetVariable('parent_id', $comment['gadget_reference']);
            $tpl->SetVariable('name',  $comment['name']);
            $tpl->SetVariable('email', $comment['email']);
            $tpl->SetVariable('url',   $comment['url']);
            $tpl->SetVariable('title', $comment['title']);
            $tpl->SetVariable('ip_address', '127.0.0.1');
            $tpl->SetVariable('status_message', '&nbsp;');
            $tpl->SetVariable('avatar_source', $comment['avatar_source']);
            $tpl->SetVariable('replies', $comment['replies']);
            $tpl->SetVariable('commentname', 'comment' . $comment['id']);
            $commentsText = $this->ParseText($comment['msg_txt']);
            $tpl->SetVariable('comments', $commentsText);
            $tpl->SetVariable('createtime-iso',       $comment['createtime']);
            $tpl->SetVariable('createtime',           $date->Format($comment['createtime']));
            $tpl->SetVariable('createtime-monthname', $date->Format($comment['createtime'], 'MN'));
            $tpl->SetVariable('createtime-monthabbr', $date->Format($comment['createtime'], 'M'));
            $tpl->SetVariable('createtime-month',     $date->Format($comment['createtime'], 'm'));
            $tpl->SetVariable('createtime-dayname',   $date->Format($comment['createtime'], 'DN'));
            $tpl->SetVariable('createtime-dayabbr',   $date->Format($comment['createtime'], 'D'));
            $tpl->SetVariable('createtime-day',       $date->Format($comment['createtime'], 'd'));
            $tpl->SetVariable('createtime-year',      $date->Format($comment['createtime'], 'Y'));
            $tpl->SetVariable('createtime-time',      $date->Format($comment['createtime'], 'g:ia'));
            $tpl->SetVariable('level', 0);
            $tpl->ParseBlock('comment');
        }

        return $tpl->Get();
    }

    /**
     * Displays a given blog comments and a form for replying
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Reply()
    {
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('id', 'comment_id'), 'get');
        $postHTML = $GLOBALS['app']->LoadGadget('Blog', 'HTML', 'Post');
        return $postHTML->SingleView($post['id'], false, (int)$post['comment_id']);
    }

    /**
     * Displays a form to send a comment to the blog
     *
     * @access  public
     * @param   int     $parent_id  id of the replied item(immediately before on the thread)
     * @param   int     $parent     id of the replied entry(comment thread starter)
     * @param   string  $title      title of the comment
     * @param   string  $comments   body of the comment(optional, empty by default)
     * @return  string  XTHML template content
     */
    function DisplayCommentForm($parent_id, $parent = 0, $title = '', $comments = '')
    {
        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('CommentForm.html');
        $tpl->SetBlock('commentform');

        $post = $GLOBALS['app']->Session->PopSimpleResponse('Blog_Comment');

        if (!$GLOBALS['app']->Session->Logged()) {
            $tpl->SetBlock('commentform/unregistered');
            // Get person info from cookie or post...
            if (!is_null($post['name'])) {
                $visitorName = $post['name'];
            } elseif ($GLOBALS['app']->Session->GetCookie('visitor_name')) {
                $visitorName = $GLOBALS['app']->Session->GetCookie('visitor_name');
            } else {
                $visitorName = '';
            }

            if (!is_null($post['email'])) {
                $visitorEmail = $post['email'];
            } elseif ($GLOBALS['app']->Session->GetCookie('visitor_email')) {
                $visitorEmail = $GLOBALS['app']->Session->GetCookie('visitor_email');
            } else {
                $visitorEmail = '';
            }

            if (!is_null($post['url'])) {
                $visitorUrl = $post['url'];
            } elseif ($GLOBALS['app']->Session->GetCookie('visitor_url')) {
                $visitorUrl = $GLOBALS['app']->Session->GetCookie('visitor_url');
            } else {
                $visitorUrl = 'http://';
            }

            $tpl->SetVariable('name', _t('GLOBAL_NAME'));
            $tpl->SetVariable('name_value', $visitorName);
            $tpl->SetVariable('email', _t('GLOBAL_EMAIL'));
            $tpl->SetVariable('email_value', $visitorEmail);
            $tpl->SetVariable('url',  _t('GLOBAL_URL'));
            $tpl->SetVariable('url_value', $visitorUrl);
            $tpl->ParseBlock('commentform/unregistered');
        }

        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        if ($mPolicy->LoadCaptcha($captcha, $entry, $label, $description)) {
            $tpl->SetBlock('commentform/captcha');
            $tpl->SetVariable('lbl_captcha', $label);
            $tpl->SetVariable('captcha', $captcha);
            if (!empty($entry)) {
                $tpl->SetVariable('captchavalue', $entry);
            }
            $tpl->SetVariable('captcha_msg', $description);
            $tpl->ParseBlock('commentform/captcha');
        }

        if (!is_null($post['title'])) {
            $title = $post['title'];
        }

        if (!is_null($post['comments'])) {
            $comments = $post['comments'];
        }

        if (!is_null($post['parent'])) {
            $parent = $post['parent'];
        }

        $tpl->SetVariable('title', _t('BLOG_LEAVE_COMMENT'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('parent_id',   $parent_id);
        $tpl->SetVariable('parent', $parent);
        $tpl->SetVariable('gadget', 'Blog');
        $tpl->SetVariable('action', 'SaveComment');

        // Test to see if this does any good to reduce spam
        $tpl->SetVariable('url2', _t('GLOBAL_SPAMCHECK_EMPTY'));
        $tpl->SetVariable('url2_value',  '');
        $tpl->SetVariable('comment_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('title_value', $title);
        $tpl->SetVariable('comments', _t('BLOG_COMMENT'));
        $tpl->SetVariable('comments_value', $comments);

        $tpl->SetVariable('lbl_feeds', _t('BLOG_COMMENTS_XML'));
        $tpl->SetVariable('atom_url', $this->GetURLFor('CommentsAtom', array('id' => $parent_id)));
        $tpl->SetVariable('rss_url',  $this->GetURLFor('CommentsRSS',  array('id' => $parent_id)));

        $tpl->SetVariable('send',    _t('BLOG_SUBMIT_COMMENT'));
        $tpl->SetVariable('preview',    _t('GLOBAL_PREVIEW'));

        /*
        if ($this->GetRegistry('mailer', 'Settings') !== 'DISABLED') {
            $tpl->SetBlock('commentform/mail_me');
            $tpl->SetVariable('mail_me', _t('BLOG_MAIL_COMMENT_TO_ME'));
            $tpl->ParseBlock('commentform/mail_me');
        }
        */

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Blog')) {
            $tpl->SetBlock('commentform/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('commentform/response');
        }

        $tpl->ParseBlock('commentform');

        return $tpl->Get();
    }

    /**
     * Displays a preview of the given blog comment
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Preview()
    {
        $request =& Jaws_Request::getInstance();
        $names = array(
            'name', 'email', 'url', 'title', 'comments', 'createtime',
            'ip_address', 'parent_id', 'parent'
        );
        $post = $request->get($names, 'post');
        $id   = (int)$post['parent_id'];
        $GLOBALS['app']->Session->PushSimpleResponse($post, 'Blog_Comment');

        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        $entry = $model->GetEntry($id, true);
        if (Jaws_Error::isError($entry)) {
            $GLOBALS['app']->Session->PushSimpleResponse($entry->getMessage(), 'Blog');
            Jaws_Header::Location($this->GetURLFor('DefaultAction'), true);
        }

        $postHTML = $GLOBALS['app']->LoadGadget('Blog', 'HTML', 'Post');
        $id = !empty($entry['fast_url']) ? $entry['fast_url'] : $entry['id'];
        return $postHTML->SingleView($id, true);
    }

    /**
     * Displays a preview of the given blog comment
     *
     * @access  public
     * @return  string XHTML template content
     */
    function ShowPreview()
    {
        $post = $GLOBALS['app']->Session->PopSimpleResponse('Blog_Comment', false);
        if ($GLOBALS['app']->Session->Logged()) {
            $post['name']  = $GLOBALS['app']->Session->GetAttribute('nickname');
            $post['email'] = $GLOBALS['app']->Session->GetAttribute('email');
            $post['url']   = $GLOBALS['app']->Session->GetAttribute('url');
        }

        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('Comment.html');
        $tpl->SetBlock('comment');

        $tpl->SetVariable('name',  $post['name']);
        $tpl->SetVariable('email', $post['email']);
        $tpl->SetVariable('url',   $post['url']);
        if (is_null($post['ip_address'])) {
            $post['ip_address'] = $_SERVER['REMOTE_ADDR'];
        }
        $tpl->SetVariable('title', $post['title']);
        $tpl->SetVariable('comments', $this->ParseText($post['comments']));
        if (!isset($post['createtime'])) {
            $date = $GLOBALS['app']->loadDate();
            $post['createtime'] = $date->Format(time());
        }
        $tpl->SetVariable('createtime', $post['createtime']);
        $tpl->SetVariable('level', 0);
        $tpl->SetVariable('status_message', '&nbsp;');
        $tpl->SetVariable('ip_address', $post['ip_address']);
        $tpl->SetVariable('avatar_source', 'images/unknown.png');
        $tpl->SetVariable('replies', '0');
        $tpl->SetVariable('commentname', 'comment_preview');

        $tpl->ParseBlock('comment');
        return $tpl->Get();
    }

    /**
     * Saves the given blog comment
     *
     * @access  public
     */
    function SaveComment()
    {
        $request =& Jaws_Request::getInstance();
        $names = array(
            'name', 'email', 'url', 'title', 'comments', 'createtime',
            'ip_address', 'parent_id', 'parent', 'url2'
        );
        $post = $request->get($names, 'post');
        $id  = (int)$post['parent_id'];

        if ($GLOBALS['app']->Session->Logged()) {
            $post['name']  = $GLOBALS['app']->Session->GetAttribute('nickname');
            $post['email'] = $GLOBALS['app']->Session->GetAttribute('email');
            $post['url']   = $GLOBALS['app']->Session->GetAttribute('url');
        }

        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        $entry = $model->GetEntry($id, true);
        if (Jaws_Error::isError($entry)) {
            $GLOBALS['app']->Session->PushSimpleResponse($entry->getMessage(), 'Blog');
            Jaws_Header::Location($this->GetURLFor('DefaultAction'), true);
        }

        $id = !empty($entry['fast_url']) ? $entry['fast_url'] : $entry['id'];
        $url = $this->GetURLFor('SingleView', array('id' => $id));

        $allow_comments_config = $GLOBALS['app']->Registry->Get('/gadgets/Settings/allow_comments');
        $restricted = $allow_comments_config == 'restricted';
        $allow_comments_config = $restricted? $GLOBALS['app']->Session->Logged() : ($allow_comments_config == 'true');

        // Check if comments are allowed.
        if ($entry['allow_comments'] !== true ||
            $this->GetRegistry('allow_comments') != 'true' ||
            !$allow_comments_config)
        {
            Jaws_Header::Location($url, true);
        }

        /* lets check if it's spam
         * it's rather common that spam engines
         * fill out all inputs and this one is hidden
         * via CSS so not many engines are smart enough
         * to not fill this out
         */
        if (!empty($post['url2'])) {
            Jaws_Header::Location($url, true);
        }

        if (trim($post['name']) == '' || trim($post['title']) == '' || trim($post['comments']) == '') {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('GLOBAL_ERROR_INCOMPLETE_FIELDS'), 'Blog');
            $GLOBALS['app']->Session->PushSimpleResponse($post, 'Blog_Comment');
            Jaws_Header::Location($url, true);
        }

        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        $resCheck = $mPolicy->CheckCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $GLOBALS['app']->Session->PushSimpleResponse($resCheck->getMessage(), 'Blog');
            $GLOBALS['app']->Session->PushSimpleResponse($post, 'Blog_Comment');
            Jaws_Header::Location($url, true);
        }

        $result = $model->NewComment($post['name'], $post['title'], $post['url'],
                           $post['email'], $post['comments'], $post['parent'],
                           $post['parent_id']);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($result->getMessage(), 'Blog');
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('GLOBAL_MESSAGE_SENT'), 'Blog');
        }

        Jaws_Header::Location($url, true);
    }

}