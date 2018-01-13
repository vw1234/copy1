<?php

/*

CometChat
Copyright (c) 2016 Inscripts

CometChat ('the Software') is a copyrighted work of authorship. Inscripts
retains ownership of the Software and any copies of it, regardless of the
form in which the copies may exist. This license is not a sale of the
original Software or any copies.

By installing and using CometChat on your server, you agree to the following
terms and conditions. Such agreement is either on your own behalf or on behalf
of any corporate entity which employs you or which you represent
('Corporate Licensee'). In this Agreement, 'you' includes both the reader
and any Corporate Licensee and 'Inscripts' means Inscripts (I) Private Limited:

CometChat license grants you the right to run one instance (a single installation)
of the Software on one web server and one web site for each license purchased.
Each license may power one instance of the Software on one domain. For each
installed instance of the Software, a separate license is required.
The Software is licensed only to you. You may not rent, lease, sublicense, sell,
assign, pledge, transfer or otherwise dispose of the Software in any form, on
a temporary or permanent basis, without the prior written consent of Inscripts.

The license is effective until terminated. You may terminate it
at any time by uninstalling the Software and destroying any copies in any form.

The Software source code may be altered (at your risk)

All Software copyright notices within the scripts must remain unchanged (and visible).

The Software may not be used for anything that would represent or is associated
with an Intellectual Property violation, including, but not limited to,
engaging in any activity that infringes or misappropriates the intellectual property
rights of others, including copyrights, trademarks, service marks, trade secrets,
software piracy, and patents held by individuals, corporations, or other entities.

If any of the terms of this Agreement are violated, Inscripts reserves the right
to revoke the Software license at any time.

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

if (!defined('CCADMIN')) { echo "NO DICE"; exit; }

$navigation = <<<EOD
	<div id="leftnav">
	</div>
EOD;

function index() {
	if (empty($GLOBALS['client'])) { echo "Not Found"; exit; }

	global $body;
	global $client;
	global $jslink;
	global $csslink;
	global $cms;
	global $pluginkey;

	$code = '';
	if(file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'addcometchat'.DIRECTORY_SEPARATOR.$cms.'.php')) {
		$code = include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'addcometchat'.DIRECTORY_SEPARATOR.$cms.'.php');
	}

$body = <<<EOD
	<h2>Add CometChat</h2>
	<h3>Add CometChat to your site.</h3>
	<div id="addcometchat_container">
		{$code}
		<div class="addcometchat_box">
			<div class="titlebox">
				<h6>Add CometChat as a floating bar to your site</h6>
			</div>
			<div style="clear:both;"></div>
			<div class="image">
				<img id = "anchor" onclick="javascript:showModal(this);" title = "Docked Bar" alt = "Docked Bar" src="images/cometchat.png" style="max-width:100%;height:142px;">
			</div>
			<div style="clear:both;"></div>
			<div><p>Copy the code below and paste it just before the </head> tag to add CometChat as a floating bar.</p></div>
			<div style="clear:both;"></div>
			<div class="textarea_container">
				<textarea id="code" onclick="javascript:selectText(this);" rows="4" style = "resize:none;cursor:copy;" readonly>&lt;link type="text/css" rel="stylesheet" href="{$csslink}" /&gt;&#13;&lt;script type="text/javascript" src="{$jslink}"&gt;&lt;/script&gt;</textarea>
			</div>
		</div>
		<div class="addcometchat_box">
			<div class="titlebox">
				<h6>Embed CometChat in your site page</h6>
			</div>
			<div style="clear:both;"></div>
			<div class="image">
				<img id = "anchor" onclick="javascript:showModal(this);" style="max-width:100%;height:142px;" title = "Synergy" alt = "Synergy" src="images/synergy.png">
			</div>
			<div style="clear:both;"></div>
			<p style="padding-left:18px">Copy/paste the code below to embed CometChat into your site page:</p>
			<div class="textarea_container">
				<textarea id="code_embed" onclick="javascript:selectText(this);" rows="4" style = "resize:none;cursor:copy;" readonly>&lt;div id="cometchat_embed_synergy_container" style="width: 100%; height: 420px;max-width:100%;border:1px solid #CCCCCC;" >&lt;/div&gt;&#13;&lt;script src="<?php echo BASE_URL;?>js.php?type=core&name=embedcode" type="text/javascript"&gt;&lt;/script&gt;&lt;script&gt;var iframeObj = {};iframeObj.module="synergy";iframeObj.style="min-height:350px;min-width:300px;";iframeObj.src="<?php echo BASE_URL;?>cometchat_popout.php"; if(typeof(addEmbedIframe)=="function"){addEmbedIframe(iframeObj);}&#13;&lt;/script&gt;</textarea>
			</div>
			<div id="ccmodal" class="modal">
			  <div class="modal-content">
			  	<div id="image"></div>
			  </div>
			</div>
		</div>
	</div>
EOD;
	template();
}
