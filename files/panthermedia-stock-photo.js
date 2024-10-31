// $Author$
// $Date$
// $Id$
// $Revision$
// $Lastlog$

/*
PantherMedia Stock Photo Plugin for WordPress
Copyright (C) 2017  PantherMedia GmbH

This file is part of PantherMedia Stock Photo Plugin for WordPress.

PantherMedia Stock Photo Plugin for WordPress is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or any later version.

PantherMedia Stock Photo Plugin for WordPress is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with PantherMedia Stock Photo Plugin for WordPress.  If not, see <http://www.gnu.org/licenses/>.

Contributor(s): PantherMedia GmbH (http://www.panthermedia.net), Steffen Blaszkowski
*/
jQuery(document).ready(function($) {
   
   /* #############################################
    * VARS
    */
   // search
   var siteSearch = $('.pm_media_search');
   var siteDetail = $('.pm_media_detail');
   var h1Detail = $('.pm_media_detail_h1');

   var $start = $('#pm_ajax_start');
   var $button = $('.pm_search_button');

   var $status = $('.panthermedia_paging_info');
   var $images = $('.pm_search_images');
   var $pagination = $('.panthermedia_paging');
   var $advSearch = $('#pm_search_adv');
   var $advSearchFields = $('.panthermedia_search_advanced');
   var $form = $('#pm_search_filter');
   var $errorInfo = $('#panthermedia_error_info');

   var $input = $('.pm_search_input');
   var $filterSort = $('#filter-sort');
   var $perPage = $('#pm_per_page');

   var $filterOn = "&#9660;";
   var $filterOff = "&#9654;";
   
   var $corporateImages = false;
   
   
   // notice vars
   var pmNoticeSuccess = '<font style="color: green;">&#10003;</font>';
   var pmNoticeError = '<font style="color: red;">&#10007;</font>';
   var $pmNoticeMessage = $('.panthermedia_notice_message');
   var $pmNoticeTitle = $('.panthermedia_loading_notice h2 font');
   var $pmNoticeLoader = $('.panthermedia_notice_loader');
   var $pmLoading = $('.panthermedia_loading');
   var $pmCloseButton = $('.panthermedia_loading_notice h2 button');
   
   
   /* #############################################
    * when select is loaded
    */
   function corpVal() {
      return ($('#corporateImages select').length>0) ? $('#corporateImages select').val() : 'no';
   }
   
   
   /* #############################################
    * notice container position
    */
   if($('.panthermedia_loading_notice').length > 0) { wrapSize(); }
   function wrapSize() {
      var $e = $('.panthermedia_loading_notice');
      
      var $noticeWidth = parseInt( $e.css('width').replace('px','') );
      var $left = ( ( $(window).width() - $noticeWidth ) / 2 );
      
      $e.css( 'left' , $left );
      return true;
   }
   
   
   
   /* #############################################
    * controll form fields
    */
   function checkForm($form) {
      $('.'+$form+' input').each(function(){
         if($(this).attr('type') === 'number') {
            var value = parseInt($(this).val());
            if(value < $(this).attr('min')) {
               $(this).val($(this).attr('min'));
            } else if(value > $(this).attr('max')) {
               $(this).val($(this).attr('max'));
            }
         }
      });
   }
   
   /* #############################################
    * include linebreak in text
    */
   function textbreak(imsg, size) {
      var tmp = imsg;
      imsg = '';
      while(tmp.length > 0){
         imsg += tmp.substr(0,size)+'<br>';
         tmp = tmp.substr(40, tmp.length);
      }
      
      return imsg;
   }
   
   
   /* #############################################
    * tab activation
    */
   var $tab = $('.panthermedia_tab');
   var $divs = $('.panthermedia_tab_div');
   var $radio = $('.panthermedia_tab_radio');
   var $checked = $('.panthermedia_tab_radio:checked');
   $checked.parent('label').addClass('active');
   $('.panthermedia_tab_div_'+ $checked.val() ).show();
   $radio.bind('change', function() {
      $divs.hide();
      $('.panthermedia_tab_div_'+ this.value ).show();
      $tab.children('label.active').removeClass('active');
      $(this).parent('label').addClass('active');
   });
   
   
   
   /* #############################################
    * hide notice box
    */
   $('.panthermedia_loading_notice h2 button').click(function(){
      $pmLoading.hide();
   });
   
   

   /* #############################################
    * hexcode - hidden checkbox
    */ 
   var $hInput = $('#panthermedia_hexcode_input');
   var $hSpan = $('#panthermedia_hexcode_span');
   var $hColor = $('#panthermedia_hexcode_color');
   // change color-option
   $hInput.change(function() {
      if($hInput.prop('checked') === true) {
         $hColor.val( $hColor.data('value') );
         $hColor.attr('type','color');
         $hSpan.html( $hSpan.data('active') );
      } else {
         $hColor.data('value', $hColor.val());
         $hColor.attr('type','hidden');
         $hColor.val('');
         $hSpan.html( $hSpan.data('inactive') );
      }
   });
   
   
   
   /* #############################################
    * advanced search
    */
   // show|hide advanced search
   $advSearch.bind('click', function() {
      if($advSearchFields.css('display') === 'block') {
         $advSearchFields.fadeOut(500);
      } else {
         $advSearchFields.fadeIn(500);
      }
      window.setTimeout(function() {
         width();
      }, 600);
   });
   
   $( window ).resize(function() {
      width();
   });
   
   // width from image-container with advanced search or not
   if($('.pm_media_search').length>0) { width(); }
   function width() {
      var w = $('.pm_media_search').width();
      var l = $advSearchFields.width();
      var placeSize = 20;
      var minWidth = 175;
      var newWidth = 0;

      if($advSearchFields.css('display') === 'block' && $advSearchFields.height() > 0) {
         newWidth = w - l - placeSize;
         $advSearch.children('span').html($filterOn);
         $('.panthermedia_search_result').css('width', (newWidth<minWidth) ? minWidth : newWidth);
      } else {
         newWidth = w - placeSize;
         $advSearch.children('span').html($filterOff);
         $('.panthermedia_search_result').css('width', (newWidth<minWidth) ? minWidth : newWidth);
      }
   }
   
   // on loading arrow v >
   $('.panthermedia_search_advanced_element div span').html(' ');
   if($('.panthermedia_search_advanced_element label').css('display') === 'none') {
      window.setTimeout(function() {
         $('.panthermedia_search_advanced_element div span').html($filterOff);
      }, 500);
   } else {
      $('.panthermedia_search_advanced_element div span').html($filterOn);
   }
   // on click arrow v >
   $('.panthermedia_search_advanced_element div').click(function(){
      var label = $(this).parent('div').children('label');
      if(label.css('display') === 'block') {
         label.css('display','none');
         $(this).children('span').html($filterOff);
      } else {
         label.css('display','block');
         $(this).children('span').html($filterOn);
      }
   });
   
   
   
   /* #############################################
    * mymedia and search
    */
   if($start.val() === "1") {
      if($start.data('type') === 'mymedia') {
         mymedia();
      }
      else if($start.data('type') === 'media') {
         search();
      }
   }
   
   // search button
   $button.bind('click', function() {
      if($button.data('type') === 'mymedia') {
         mymedia();
      }
      else if($button.data('type') === 'search') {
         search();
      }
   });
   
   
   /* #############################################
    * starting requests
    */
   // when filter file not exists
   if($('#getFilter').length>0) {
      var obj = { 'a': 'saveSettings-getFilter' };
      request(obj);
   }
   
   // clear cache
   $('.panthermedia_clear_cache').on('click', function(){
      if(confirm(PantherMediaStockPhoto.messages.confirm)) {
         var obj = { 'a': 'clearCache' };
         request(obj, true);
      }
   });
   
   // settings save
   $('.panthermedia_settings_save').bind('click', function(){
      if(!$(this).data('check') || $(this).data('check') && confirm(PantherMediaStockPhoto.messages.confirm)) {
         checkForm( 'panthermedia_tab_div_'+$(this).data('type')+' form' );
         var obj = {
            'a': 'saveSettings-'+$(this).data('type'),
            'data': $('.panthermedia_tab_div_'+$(this).data('type')+' form').serializeArray()
         };
         request(obj, $(this).data('check'));
      }
   });
   
   // login
   $('#panthermedia_openauth').bind('click', function() {
      var obj = { 'a': 'openauth' };
      request(obj);
   });
   
   // logout
   $('#panthermedia_logout').bind('click', function() {
      var obj = { 'a': 'logout' };
      request(obj, true);
   });
   
   // ajax mymedia
   function mymedia(page) {
      
      var obj = { 
            'a': 'media_licenses',
            'page': (page) ? page : 1,
            'per_page': (parseInt($perPage.val()) > 0) ? $perPage.val() : 24,
            'corporate': corpVal()
         };
      request(obj, false, 'search');
   }
   
   // ajax search
   function search(page) {
      var obj = {
            'a': 'media_search',
            'page': (page) ? page : 1,
            'per_page': (parseInt($perPage.val()) > 0) ? $perPage.val() : 24,
            'q': $input.val(),
            'sort': $filterSort.val(),
            'data': $form.serializeArray()
         };
      request(obj, false, 'search');
    }
    
   // ajax detail
   function detail(id) {
      var obj = {
            'a': 'media_detail',
            'id': id,
            'corporate': corpVal()
         };
      request(obj, false, 'detail', id);
   }
    
   // ajax available
   function available(id) {
      /*if(parseInt(download) === 0) {
         alert('Error');
         return;
      }*/
      var obj = {
            'a': 'media_available',
            'id': id,
            'corporate': corpVal()
         };
      request(obj, false, 'available', id);
   }
   
   // ajax download image
   function download_image(id, download) {
      if(confirm(PantherMediaStockPhoto.messages.confirm + ' ' + PantherMediaStockPhoto.messages.testmode)) {
         var obj = {
            'a': 'image_download',
            'id': id,
            'download': download
         };
         request(obj, false, 'download', id, download);
      }
   }
   
   // ajax buy
   function buy_image(id, article) {
      if(confirm(PantherMediaStockPhoto.messages.confirm + ' ' + PantherMediaStockPhoto.messages.testmode)) {
         var obj = {
            'a': 'image_buy',
            'id': id,
            'article': article
         };
         request(obj, false, 'buy', id);
      }
   }
   
   
   
   /* #############################################
    * ajax requests
    * data => post-data
    * reload => auto-reload the current site after request
    * site => reload functions for search, detail, download and buy
    * imageID => image id to info or download/buy
    * downloadID => download id to (re-)download image
    */
   function request(data, reload, site, imageID, downloadID) {
      $pmNoticeMessage.hide();
      $pmCloseButton.hide();
      $pmNoticeLoader.css('display', 'table-cell');
      $pmLoading.show();
      
      // request
      $.post(PantherMediaStockPhoto.ajax_url, data,

         // success
         function(r) {
            if(r.debug && r.debug === true) {
               //console.log(r);
               debugOutFb(r);
            }
            
            var message = (r.info) ? r.info : r.message;
            
            message = message.replace(/\r|\n/g, '<br>');
            var split = message.split('Internal:<br>');
            if(split[1] !== undefined && split[1].length > 0) {
               var internal = textbreak(split[1], 50);
               message = split[0] + 'Internal:<br>' + internal;
            }
            
            $pmNoticeTitle.html(PantherMediaStockPhoto.messages.error);
            $pmNoticeMessage.html( pmNoticeError +' ['+r.status+'] '+message);
            $pmNoticeMessage.css('display', 'table-cell');
            $pmNoticeMessage.css('height', 'auto');
            $pmNoticeLoader.hide();
            $pmCloseButton.show();

            if(r.status === 200) {
               
               $pmNoticeTitle.html(PantherMediaStockPhoto.messages.success);
               $pmNoticeMessage.html( pmNoticeSuccess +' ['+r.status+'] '+message);
               $status.html(r.info);
               $images.html(r.images);
               $errorInfo.html(r.error_info);
               $corporateImages = r.corporate_images;
               if($corporateImages === true) {
                  $('#corporateImages').show();
               }
               
               if(site==='search') {
                  $pagination.html(r.paging);
                  stickytooltip.init('data-tooltip-pm', 'panthermedia_sticky');
                  
                  $('.panthermedia_paging_pager input[type=number]').on('change', function(){
                     $('.panthermedia_paging_pager button').show();
                     $('.panthermedia_paging_pager input[type=number]').val( $(this).val() );
                  });
                  
                  $('.panthermedia_paging_pager button').on('click', function(){
                     var page = $('.panthermedia_paging_pager input[type=number]').val();
                     if($button.data('type') === 'mymedia') {
                        mymedia( page );
                     }
                     else if($button.data('type') === 'search') {
                        search( page );
                     }
                  });
                  
                  $('a.panthermedia_page').bind('click', function() {
                     if($button.data('type') === 'mymedia') {
                        mymedia( $(this).attr('href') );
                     }
                     else if($button.data('type') === 'search') {
                        search( $(this).attr('href') );
                     }
                  });
                  
                  $('a.panthermedia_action').bind('click', function() {
                     if($(this).data('type') === 'detail') {
                        detail( $(this).attr('href').replace('#','') );
                     }
                     else if($(this).data('type') === 'available') {
                        available( $(this).attr('href').replace('#','') );
                     }
                     else if($(this).data('type') === 'download') {
                        download_image($(this).attr('href').replace('#',''), $(this).data('download'));
                     }
                  });
               }
               
               if(site==='detail' || site==='available') {
                  siteSearch.hide();
                  siteDetail.show();
                  h1Detail.show();
                  siteDetail.html(r.detail);

                  // click on "back"-button
                  $('.panthermedia_detail_back').bind('click', function() {
                     siteSearch.show();
                     siteDetail.hide();
                     h1Detail.hide();
                  });

                  // click on "reload"-button
                  $('.panthermedia_detail_reload').bind('click', function() {
                     detail(imageID);
                  });
                  $('.panthermedia_download_reload').bind('click', function() {
                     available(imageID);
                  });
                  
                  $('.panthermedia_download_image').bind('click', function() {
                     download_image($(this).data('media'),$(this).data('download'));
                  });

                  // method tab
                  var $checked = $('.panthermedia_tab_radio:checked');
                  $checked.parent('label').addClass('active');
                  $('.panthermedia_tab_div_'+ $checked.val() ).show();
                  $('.panthermedia_tab_radio').bind('change', function() {
                     $('.panthermedia_tab_div').hide();
                     $('.panthermedia_tab_div_'+ this.value ).show();
                     $('.panthermedia_tab').children('label.active').removeClass('active');
                     $(this).parent('label').addClass('active');
                  });

                  // method table
                  $('.panthermedia_tab_div tr').on('click', function() {
                     var $child = $(this).children().children('input[type=radio]');
                     var $l1 = $('input[name=pm_buy_license]:checked');
                     if($child.attr('disabled') === undefined && $l1.val() !== undefined) {
                        $child.prop('checked', true);
                        $('.panthermedia_buy_media').removeAttr('disabled');
                        $('.panthermedia_buy_media').html(PantherMediaStockPhoto.messages.download);
                     }
                     costs();
                  });

                  // credit-costs
                  function costs() {
                     var $l1 = $('input[name=pm_buy_license]:checked');
                     var $l2 = $('input[name=pm_buy_license_er]:checked');
                     var cost = parseInt($l1.data('cost')) + parseInt($l2.data('cost'));
                     $('#panthermedia_total_credits').html(cost);
                  }
                  
                  // buy image
                  $('.panthermedia_buy_media').on('click', function() {
                     var $size = $('.panthermedia_tab_div_credits input[name=pm_buy_license]:checked');
                     var $sizeEr = $('.panthermedia_tab_div_credits input[name=pm_buy_license_er]:checked');
                     var $id = $(this).data('id');
                     if($size.val() !== undefined) {
                        if($sizeEr.val() === 'false') {
                           buy_image($id, $size.val());
                        } else {
                           buy_image($id, $size.val()+','+$sizeEr.val());
                        }
                     }
                  });
                  
                  // buy image
                  $('.panthermedia_buy_media_full').on('click', function() {
                     var $id = $(this).data('id');
                     var $article = $(this).data('article');
                     buy_image($id, $article);
                  });
               }
               
               var timeout = false;
               if(reload===true || r.reload || site==='download' || site==='buy') 
               { timeout = true; }
               
               if(timeout) {
                  $pmCloseButton.hide();
                  window.setTimeout(function() {
                     if(reload===true) { window.location.href = window.location.href; }
                     else if(r.reload) { window.location.href = r.reload; }
                     else if(site==='download') { available(imageID); } 
                     else if(site==='buy') { detail(imageID); }
                     else { $pmLoading.hide(); }
                  }, 2000);
               }
               else { $pmLoading.hide(); }
            }
         })

         // error
         .error(function(xhr, status, error) {
            $pmNoticeMessage.html(pmNoticeError+' '+status+': '+xhr.responseText);
            $pmNoticeMessage.css('display', 'table-cell');
            $pmNoticeLoader.hide();
            $pmCloseButton.show();
      });
   }
});


//--------------------------------------------------------------------
// next lines are part of crVCL PHP Framework
/* 

The contents of this file are subject to the Mozilla Public License
Version 1.1 (the "License"); you may not use this file except in compliance
with the License. You may obtain a copy of the License at
http://www.mozilla.org/MPL/MPL-1.1.html or see MPL-1.1.txt in directory "license"

Software distributed under the License is distributed on an "AS IS" basis,
WITHOUT WARRANTY OF ANY KIND, either expressed or implied. See the License for
the specific language governing rights and limitations under the License.

The Initial Developers of the Original Code are: 
Copyright (c) 2003-2017, CR-Solutions (http://www.cr-solutions.net), Ricardo Cescon
All Rights Reserved.

crVCL PHP Framework Version 2.8
*/

function is_firebug_present(){
   try{
      if(console && console.firebug && window.loadFirebugConsole)return true; // old firebug versions
      if(console.log)return true; // new firebug versions and IE version >= 10
   }catch(e){}
   
   return false;
}     
//--------------------------------------------------------------------
var FB_DEBUG = 0;
var FB_INFO = 1;
var FB_WARN = 2;
var FB_ERROR = 3;
function debugOutFb(msg, type){
   try{
      if((typeof type) != 'number'){
         type = 0;
      }
      if(type == 0 && isIE()){
         type = 1;
      }
      if(is_firebug_present()){
         switch(type){
            case 1:
               console.info(msg);
               break;
            case 2:
               console.warn(msg);
               break;
            case 3:
               console.error(msg);
               break;

            default:
               console.log(msg);
         }
         return true;
      }
   }catch(e){}
   return false;
}
function isIE(returnVersion){
      var userAgent = navigator.userAgent.toLowerCase();
      var checkstr = "msie";
      var delimiter = ";";
      var MSIEOffset = userAgent.indexOf(checkstr);
            
      if (MSIEOffset == -1 && userAgent.indexOf("trident") != -1){
         checkstr = "rv:";
         delimiter = ")";
         MSIEOffset = userAgent.indexOf(checkstr);
      }
      
      if (MSIEOffset != -1){
         if((typeof returnVersion) == 'boolean' && returnVersion){
            return parseFloat(userAgent.substring(MSIEOffset + checkstr.length, userAgent.indexOf(delimiter, MSIEOffset)));;
         }
         return true;
      }
      else {
         if((typeof returnVersion) == 'boolean' && returnVersion){
            return -1;
         }
         return false;
      }
}