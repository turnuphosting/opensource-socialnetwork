<?php
/**
 * Open Source Social Network
 *
 * @package   (softlab24.com).ossn
 * @author    OSSN Core Team <info@softlab24.com>
 * @copyright 2014-2017 SOFTLAB24 LIMITED
 * @license   Open Source Social Network License (OSSN LICENSE)  http://www.opensource-socialnetwork.org/licence
 * @link      https://www.opensource-socialnetwork.org/
 */
?>
<div class="message-sender" id="ossn-message-item-<?php echo $params['id'];?>">
    <div class="ossn-chat-text-data-right">
        <div class="ossn-chat-triangle ossn-chat-triangle-blue"></div>
        <div class="text <?php echo $params['class'];?>">
            <div class="inner" title="<?php echo OssnChat::messageTime($params['time']); ?>">
            	<?php if($params['deleted']){ ?>
                	<span><i class="fa fa-times-circle"></i><?php echo ossn_print('ossnmessages:deleted');?></span>
                <?php } else { ?>
	                <span><?php echo ossn_call_hook('chat', 'message:smilify', null, linkify_chat($params['message'])); ?></span>
                <?php } ?>    
            </div>
        </div>
    </div>
</div>
