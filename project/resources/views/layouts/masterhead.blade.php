 {{-- @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController') --}}

 <!DOCTYPE html>

 <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

 <head>
     <meta charset="utf-8" />
     <meta content="width=device-width, initial-scale=1" name="viewport" />
     <title>Laravel</title>
     <meta name="csrf-token" content="{{ csrf_token() }}">
     <!-- Google Fonts -->
     <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
         rel="stylesheet">

     <!-- Core Styles -->
     <link href="{{ asset('assets/vendors/styles/core.css') }}" rel="stylesheet" type="text/css">
     <link href="{{ asset('assets/vendors/styles/icon-font.min.css') }}" rel="stylesheet" type="text/css">
     <link href="{{ asset('assets/vendors/styles/style.css') }}" rel="stylesheet" type="text/css">
     <link href="{{ asset('assets/vendors/styles/loader.css') }}" rel="stylesheet" type="text/css">
     <link href="{{ asset('assets/vendors/styles/custom.css') }}" rel="stylesheet" type="text/css">

     <!-- Plugins -->
     <link href="{{ asset('assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css') }}" rel="stylesheet"
         type="text/css">
     <link href="{{ asset('assets/plugins/datatables/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"
         type="text/css">
     <link href="{{ asset('assets/plugins/datatables/css/responsive.bootstrap4.min.css') }}" rel="stylesheet"
         type="text/css">
     <link href="{{ asset('assets/plugins/switchery/switchery.min.css') }}" rel="stylesheet" type="text/css">
     <link href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.css" rel="stylesheet"
         type="text/css">

     <!-- jQuery UI -->
     <link href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" rel="stylesheet">

     <!-- Font Awesome (latest version only) -->
     <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet"
         crossorigin="anonymous" referrerpolicy="no-referrer">

     <style>
         .tooltip-container {
             position: relative;
             display: inline-block;
             cursor: pointer;
         }

         .tooltip-container .tooltip-text {
             visibility: hidden;
             width: 200px;
             background-color: #555;
             color: #fff;
             text-align: center;
             border-radius: 6px;
             padding: 5px;
             position: absolute;
             z-index: 1;
             bottom: 125%;
             /* Position the tooltip above the icon */
             left: 50%;
             margin-left: -100px;
             opacity: 0;
             transition: opacity 0.3s;
         }

         .tooltip-container .tooltip-text::after {
             content: "";
             position: absolute;
             top: 100%;
             /* Arrow at the bottom of the tooltip */
             left: 50%;
             margin-left: -5px;
             border-width: 5px;
             border-style: solid;
             border-color: #555 transparent transparent transparent;
         }

         .tooltip-container:hover .tooltip-text {
             visibility: visible;
             opacity: 1;
         }


         h6 {
             display: inline-block;
             margin-right: 5px;
         }

         .svg-icon {
             width: 16px;
             height: 16px;
             fill: currentColor;
         }

         /*Table*/

         .sort-arrow {
             margin-left: 0px;
         }

         .sort-arrow.active {
             color: #1739d1ec;
         }

         .sort-arrow {
             font-family: 'dropways';
             font-size: 14px;
             color: grey;
             /* Default color */
         }

         .sort-asc::before {
             content: "\eabb";
             /* Custom icon content for up arrow */
         }

         .sort-desc::before {
             content: "\eaba";
         }

         /* Active state with blue color */
         .sort-asc.active::before,
         .sort-desc.active::before {
             color: #1b00ff;
         }

         span.sort-arrow.sort-desc {
             margin-left: -7px;
         }

         #filter-form .form-row {
             gap: 10px;
             float: right;
             margin-top: 5px;
         }

         .searchbar {
             margin-top: 10px;
         }

         .pd-20.f-left {
             float: left;
         }


         .row ul.pagination {
             margin-bottom: 10px;
             float: right;
             margin-right: 20px;
         }

         .sort-arrow {
             margin-left: 0px;
         }

         .sort-arrow.active {
             color: #1739d1ec;
         }

         .sort-arrow {
             font-family: 'dropways';
             font-size: 14px;
             color: grey;
             /* Default color */
         }

         .sort-asc::before {
             content: "\eabb";
             /* Custom icon content for up arrow */
         }

         .sort-desc::before {
             content: "\eaba";
         }

         /* Active state with blue color */
         .sort-asc.active::before,
         .sort-desc.active::before {
             color: #1b00ff;
         }

         span.sort-arrow.sort-desc {
             margin-left: -7px;
         }


         .pd-20.f-left {
             float: left;
         }


         .row ul.pagination {
             margin-bottom: 10px;
             float: right;
             margin-right: 20px;
         }

         .select2.select2-container.select2-container--default {
             width: 100% !important;
         }

         .sort-arrow {
             margin-left: 0px;
         }

         .sort-arrow.active {
             color: #1739d1ec;
         }

         .sort-arrow {
             font-family: 'dropways';
             font-size: 14px;
             color: grey;
             /* Default color */
         }

         .sort-asc::before {
             content: "\eabb";
             /* Custom icon content for up arrow */
         }

         .sort-desc::before {
             content: "\eaba";
         }

         /* Active state with blue color */
         .sort-asc.active::before,
         .sort-desc.active::before {
             color: #1b00ff;
         }

         span.sort-arrow.sort-desc {
             margin-left: -7px;
         }


         .pd-20.f-left {
             float: left;
         }



         .row ul.pagination {
             margin-bottom: 10px;
             float: right;
             margin-right: 20px;
         }



         .form-group {
             margin-top: 5px;
         }

         h6 {
             display: inline-block;
             margin-right: 5px;
         }

         .background-icon {
             width: 16px;
             height: 16px;
             fill: currentColor;
         }

         .sort-arrow {
             margin-left: 0px;
         }

         .sort-arrow.active {
             color: #1739d1ec;
         }

         .sort-arrow {
             font-family: 'dropways';
             font-size: 14px;
             color: grey;
             /* Default color */
         }

         .sort-asc::before {
             content: "\eabb";
             /* Custom icon content for up arrow */
         }

         .sort-desc::before {
             content: "\eaba";
         }

         /* Active state with blue color */
         .sort-asc.active::before,
         .sort-desc.active::before {
             color: #1b00ff;
         }

         span.sort-arrow.sort-desc {
             margin-left: -7px;
         }


         .pd-20.f-left {
             float: left;
         }



         .row ul.pagination {
             margin-bottom: 10px;
             float: right;
             margin-right: 20px;
         }

         table.main-row-tb {
             width: 100%;
             border: 1px solid #00000024;
             border-radius: 7px;
             padding: 5px 5px;
             display: table;
             text-align: center;
         }

         table.main-row-tb thead {
             background-color: #70c8e19e;
             color: #fff;
         }

         table.row-col-tb {
             width: 100%;
             text-align: center;
         }

         table.row-col-tb tr {
             border: 1px solid #dee2e663;
         }

         .row-col-tb tr td:first-child {
             font-weight: 550;
             font-size: 15px;
         }

         table.main-row-tb.single tr td:first-child {
             font-weight: 600;
         }

         d-flex.flex-wrap.align-items-center {
             border: 1px solid #eaecec;
             border-radius: 11px;
         }

         table.main-row-tb {
             width: 100%;
             border: 1px solid #00000024;
             border-radius: 7px;
             padding: 5px 5px;
             display: table;
             text-align: center;
         }

         table.main-row-tb thead {
             background-color: #70c8e19e;
             color: #fff;
         }

         table.row-col-tb {
             width: 100%;
             text-align: center;
         }

         table.row-col-tb tr {
             border: 1px solid #dee2e663;
         }

         .row-col-tb tr td:first-child {
             font-weight: 550;
             font-size: 15px;
         }

         table.main-row-tb.single tr td:first-child {
             font-weight: 600;
         }

         d-flex.flex-wrap.align-items-center {
             border: 1px solid #eaecec;
             border-radius: 11px;
         }

         table.main-row-tb {
             width: 100%;
             border: 1px solid #00000024;
             border-radius: 7px;
             padding: 5px 5px;
             display: table;
             text-align: center;
         }

         table.main-row-tb thead {
             background-color: #70c8e19e;
             color: #fff;
         }

         table.row-col-tb {
             width: 100%;
             text-align: center;
         }

         table.row-col-tb tr {
             border: 1px solid #dee2e663;
         }

         .row-col-tb tr td:first-child {
             font-weight: 550;
             font-size: 15px;
         }

         table.main-row-tb.single tr td:first-child {
             font-weight: 600;
         }

         d-flex.flex-wrap.align-items-center {
             border: 1px solid #eaecec;
             border-radius: 11px;
         }

         .card {
             border-radius: 10px;
             width: 100%;
             padding: unset;
             height: auto;
         }

         .card-header {
             position: relative;
             display: flex;
             -webkit-box-orient: vertical;
             -webkit-box-direction: normal;
             flex-direction: column;
             min-width: 0;
             word-wrap: break-word;
             background-color: #f8f9fa;
             background-clip: border-box;
             border-bottom: 1px solid #e9ecef;
         }

         .card-header:first-child {
             border: 1px solid rgb(0 0 0 / 3%);
             border-radius: 0 0 20px 20px;
             border-bottom: 1px solid #e9ecef;
         }

         .card-body {
             width: 100%;
         }

         .card {
             border-radius: 10px;
         }

         .card-header {
             background-color: #f8f9fa;
             padding: 1rem;
             border-bottom: 1px solid #e3e7eb;
             width: 100%;
         }

         .col {
             width: 20%;
             max-width: 10%;
         }

         .spinner {
             border: 8px solid #f3f3f3;
             /* Light grey */
             border-top: 8px solid #3498db;
             /* Blue */
             border-radius: 50%;
             width: 30px;
             height: 30px;
             animation: spin 2s linear infinite;
             left: 47%;
             position: relative;
         }

         @keyframes spin {
             0% {
                 transform: rotate(0deg);
             }

             100% {
                 transform: rotate(360deg);
             }
         }

         .txtarea {
             width: 100%;
             min-height: 50px !important;
             resize: none;
             overflow-y: hidden;
             border: 1px solid #ccc;
             padding: 5px;
         }

         .sort-arrow {
             margin-left: 0px;
         }

         .sort-arrow.active {
             color: #1739d1ec;
         }

         .sort-arrow {
             font-family: 'dropways';
             font-size: 14px;
             color: grey;
             /* Default color */
         }

         .sort-asc::before {
             content: "\eabb";
             /* Custom icon content for up arrow */
         }

         .sort-desc::before {
             content: "\eaba";
         }

         /* Active state with blue color */
         .sort-asc.active::before,
         .sort-desc.active::before {
             color: #1b00ff;
         }

         span.sort-arrow.sort-desc {
             margin-left: -7px;
         }

         .sort-arrow {
             margin-left: 0px;
         }

         .sort-arrow.active {
             color: #1739d1ec;
         }

         .sort-arrow {
             font-family: 'dropways';
             font-size: 14px;
             color: grey;
             /* Default color */
         }

         .sort-asc::before {
             content: "\eabb";
             /* Custom icon content for up arrow */
         }

         .sort-desc::before {
             content: "\eaba";
         }

         /* Active state with blue color */
         .sort-asc.active::before,
         .sort-desc.active::before {
             color: #1b00ff;
         }

         span.sort-arrow.sort-desc {
             margin-left: -7px;
         }

         .sort-arrow {
             margin-left: 0px;
         }

         .sort-arrow.active {
             color: #1739d1ec;
         }

         .sort-arrow {
             font-family: 'dropways';
             font-size: 14px;
             color: grey;
         }

         .sort-asc::before {
             content: "\eabb";
         }

         .sort-desc::before {
             content: "\eaba";
         }

         .sort-asc.active::before,
         .sort-desc.active::before {
             color: #1b00ff;
         }

         span.sort-arrow.sort-desc {
             margin-left: -7px;
         }


         .pd-20.f-left {
             float: left;
         }



         .row ul.pagination {
             margin-bottom: 10px;
             float: right;
             margin-right: 20px;
         }











         h6 {
             display: inline-block;
             margin-right: 5px;
         }

         .gif-icon {
             width: 16px;
             height: 16px;
             fill: currentColor;
         }

         .sort-arrow {
             margin-left: 0px;
         }

         .sort-arrow.active {
             color: #1739d1ec;
         }

         .sort-arrow {
             font-family: 'dropways';
             font-size: 14px;
             color: grey;
             /* Default color */
         }

         .sort-asc::before {
             content: "\eabb";
             /* Custom icon content for up arrow */
         }

         .sort-desc::before {
             content: "\eaba";
         }

         /* Active state with blue color */
         .sort-asc.active::before,
         .sort-desc.active::before {
             color: #1b00ff;
         }

         span.sort-arrow.sort-desc {
             margin-left: -7px;
         }


         .pd-20.f-left {
             float: left;
         }



         .row ul.pagination {
             margin-bottom: 10px;
             float: right;
             margin-right: 20px;
         }

         div#suggestionsNewContainer {
             width: auto;
             border: 1px solid #c9bfbf5c;
             text-align: center;
             background: #FFF;
             padding: 10px 22px;
             border-radius: 6px;
             position: absolute;
             z-index: 999;
             height: auto;
             max-height: 435px;
             overflow: auto;
         }

         div#suggestionsNewContainer ul li {
             border-bottom: 1px solid #b5adad21;
             padding: 5px 0;
             cursor: pointer;
         }

         .input-container {
             position: relative;
             display: inline-block;
         }

         #searchTagsInput {
             padding-right: 20px;
             /* Add padding to make space for the icon */
         }

         .arrow-icon {
             position: absolute;
             right: 5px;
             top: 50%;
             transform: translateY(-50%);
             cursor: pointer;
             pointer-events: auto;
         }

         .bootstrap-tagsinput .tag {
             display: inline-block;
             transition: all 0.2s ease-in-out;
             margin: 2px;
             /* Add some margin for spacing */
             padding: 5px 10px;
             /* Add padding for better visual appearance */
             border-radius: 5px;
             /* Rounded corners */
             background-color: #007bff;
             /* Background color */
             color: white;
             /* Text color */
         }

         /* Highlighting the tag when dragging */
         .bootstrap-tagsinput .tag.sorting {
             box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
             /* transform: scale(1.1); */
         }

         .sp-container {
             border-radius: 10px;
             box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
         }

         .sp-picker-container {
             border-radius: 10px;
         }

         .sp-input,
         .sp-input:focus,
         .sp-input:active {
             border: none;
             outline: none;
             box-shadow: none;
         }

         .col-sm-20.color_tags {
             display: flex;
         }

         .sp-replacer {
             border-radius: 5px;
             box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
             margin-top: 5px;
             width: 50px !important;
             height: 33px;
             margin-left: 11px;
             min-width: 50px !important;
         }

         .sp-thumb-inner {
             border-radius: 4px;
         }

         #relatedKeyword .bootstrap-tagsinput.ui-sortable,
         #newKeywordsCols .bootstrap-tagsinput.ui-sortable {
             width: 100%;
         }

         .color_tags span.tag.label.label-info {
             border: 1px solid #0000000d;
             border-radius: 5px;
         }

         .h3,
         h3 {
             font-size: calc(1.3rem + .6vw) !important;
         }

         @media (min-width: 1200px) {

             .h3,
             h3 {
                 font-size: 1.75rem !important;
             }
         }

         select#selectPageStatus {
             width: auto;
             max-width: 320px;
             position: absolute;
             right: 90px;
         }

         select#selectPageType {
             width: auto;
             max-width: 320px;
             position: absolute;
             right: 207px;
         }

         .filter-row .form-control,
         .filter-row button {
             min-width: 170px;
         }

         .form-control {
             /* height: 35px !important; */
             /* font-size: 12px !important; */

         }

         .btn {
             /* height: 35px !important;
             font-size: 12px !important; */
             /* padding: 0.3rem 1rem !important; */
             /* align-items: center; */
             /* display: flex; */
         }

         @media (max-width: 768px) {
             .filter-row {
                 gap: 10px !important;
             }
         }

         .scroll-wrapper {
             flex: 1;
             overflow-y: auto;
             overflow-x: auto;
             max-height: calc(105vh - 220px);
         }

         /* .tableFixHead {
             min-width: 1000px;
         } */

         .tableFixHead thead th {
             position: sticky;
             top: -5px;
             z-index: 10;
             background-color: grey;
             color: white;
         }

         .tableFixHead td {
             word-break: break-all;
         }

         .pagination-footer {
             position: sticky;
             bottom: 0;
             background: #fff;
             z-index: 15;
             /* padding: 10px 0; */
             border-top: 1px solid #ddd;
         }


         .filter-header {
             position: sticky;
             top: 0;
             z-index: 20;
             background: #fff;
             padding: 3px 15px;
             border-bottom: 1px solid #ddd;
         }

         table tr th {
             width: 100px;
         }

         .sort-arrow {
             margin-left: 0px;
         }

         .sort-arrow.active {
             color: #1739d1ec;
         }

         .sort-arrow {
             font-family: 'dropways';
             font-size: 14px;
             color: grey;
             /* Default color */
         }

         .sort-asc::before {
             content: "\eabb";
             /* Custom icon content for up arrow */
         }

         .sort-desc::before {
             content: "\eaba";
         }

         /* Active state with blue color */
         .sort-asc.active::before,
         .sort-desc.active::before {
             color: #1b00ff;
         }

         span.sort-arrow.sort-desc {
             margin-left: -7px;
         }



         .pd-20.f-left {
             float: left;
         }



         .fa-star,
         .fa-star-half-alt {
             font-size: 16px;
             color: #ccc;
         }

         .fa-star.checked,
         .fa-star-half-alt.checked {
             color: #ffcc00;
         }

         div#DataTables_Table_0_paginate {
             float: right;
             margin-right: 5px;
         }

         .bootstrap-tagsinput {
             width: 100%;
         }

         .custom-modal-width {
             max-width: 1000px !important;
             width: 100% !important;
         }

         .common-btn {
             height: 30px;
             padding: 0 20px;
             flex: auto;
             transition: background-color 0.3s;
         }

         .common-btn:hover {
             background-color: #0056b3;
         }

         .common-btn img {
             position: absolute;
             transform: translate(40%, -50%);
             width: 1600px;
             height: auto;
             opacity: 0;
             background-color: white;
             pointer-events: none;
             transition: opacity 0.3s ease-in-out;
         }

         .input-group img {
             width: 50px;
         }

         .common-btn:hover img {
             opacity: 1;
         }

         #cta_more_template_modal .modal-dialog.modal-dialog-centered {
             width: 60vw !important;
             max-width: 60vW !important;
         }

         .select2.select2-container.select2-container--default {
             width: 100% !important;
         }

         #question {
             width: 100%;
             height: 150px !important;
         }

         .bootstrap-tagsinput input {
             padding: 0.375rem 0.75rem !important;
         }

         .bootstrap-tagsinput .tag [data-role="remove"] {
             margin-left: 8px;
             cursor: pointer;
             font-weight: 600;
         }

         span.select2.select2-container.select2-container--default.select2-container--above.select2-container--focus,
         span.select2.select2-container.select2-container--default.select2-container--focus,
         span.select2.select2-container.select2-container--default.select2-container--above,
         .select2-container {
             width: 100% !important;
         }

         .bootstrap-tagsinput {
             width: 465px !important;
         }

         .bootstrap-tagsinput .tag {
             display: inline-block;
             transition: all 0.2s ease-in-out;
             margin: 2px;
             padding: 5px 10px;
             border-radius: 5px;
             background-color: #007bff;
             color: white;
         }

         /* Highlighting the tag when dragging */
         .bootstrap-tagsinput .tag.sorting {
             box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
             /* transform: scale(1.1); */
         }

         #relatedKeyword .bootstrap-tagsinput.ui-sortable,
         #newKeywordsCols .bootstrap-tagsinput.ui-sortable {
             width: 100%;
         }

         .select2-selection {
             /* box-shadow: 0 0 10px rgba(0, 123, 255, 0.5); */
             display: grid !important;
             /* padding: 10px !important; */
         }

         .select2-selection__choice {
             /* display: inline-block; */
             transition: all 0.2s ease-in-out;
             margin: 2px;
             padding: 5px 10px;
             border-radius: 5px;
             background-color: #007bff;
             color: white;
         }

         .custom-modal-width {
             max-width: 700px !important;
             width: 100% !important;
         }



         .subca tegory {
             padding: 10px 20px;
             cursor: pointer;
             border-bottom: 1px solid #ccc;
         }

         h6 {
             display: inline-block;
             margin-right: 5px;
         }

         .svg-icon {
             width: 16px;
             height: 16px;
             fill: currentColor;
         }

         /*Table*/

         .sort-arrow {
             margin-left: 0px;
         }

         .sort-arrow.active {
             color: #1739d1ec;
         }

         .sort-arrow {
             font-family: 'dropways';
             font-size: 14px;
             color: grey;
             /* Default color */
         }

         .sort-asc::before {
             content: "\eabb";
             /* Custom icon content for up arrow */
         }

         .sort-desc::before {
             content: "\eaba";
         }

         /* Active state with blue color */
         .sort-asc.active::before,
         .sort-desc.active::before {
             color: #1b00ff;
         }

         span.sort-arrow.sort-desc {
             margin-left: -7px;
         }

         .row ul.pagination {
             margin-bottom: 10px;
             float: right;
             margin-right: 20px;
         }

         .content-modal-dialog {
             max-width: fit-content;
         }

         .img-editor {
             height: 80px;
             width: 80px;
         }

         .imageName {
             width: 30% !important;
         }

         .custom-file-upload {
             display: inline-block;
             padding: 10px 20px;
             background-color: #007bff;
             color: #fff;
             border-radius: 5px;
             cursor: pointer;
         }

         .custom-file-upload:hover {
             background-color: #0056b3;
         }

         .custom-file-upload i {
             margin-right: 5px;
         }

         .image-actions {
             height: 40px;
             width: 80%;
             margin-left: 20px;
         }

         .lccp-el-wrap {
             width: 100%;
         }

         .lccp-preview {
             width: calc(100% - 20px) !important;
             left: 10px !important;
             top: 7px !important;
             height: calc(100% - 7px - 7px) !important;
             background: linear-gradient(90deg, rgba(255, 255, 255, .4), #000);
             border-color: #ccc;
         }

         .lccp-preview-bg {
             width: calc(100% - 20px) !important;
             left: 10px !important;
             top: 7px !important;
             height: calc(100% - 7px - 7px) !important;
         }

         #colors {
             padding-left: 0px !important;
             color: white !important;
             height: 38px;
             width: 283px;
         }

         #lccp_modes_wrap {
             justify-content: center !important;
         }

         .banner-image-preview {
             height: 100px;
             width: 100px;
         }

         .content-box {
             background-color: #f8f9fa;
             padding: 20px;
             border: 1px solid #dee2e6;
             border-radius: 5px;
             margin-bottom: 20px;
         }

         .content-box img {
             max-width: 100%;
             height: auto;
             max-height: 100px;
             display: block;
             margin: 10px 0;
         }

         .ads-color-box {
             width: 30px;
             height: 20px;
             display: inline-block;
         }

         #question {
             width: 100%;
             height: 150px;
         }

         button.btn.btn-danger.removeBannerBtn {
             margin-left: 20px;
             width: 120px;
             height: 40px;
             margin-top: 40px;
             display: none;
         }

         /* 24/5/2024 */
         .resume-guide-section .btn-delete,
         .resume-content-section .btn-resume-content-delete {
             width: 99px;
             float: right;
         }

         .resume-guide-section,
         .resume-content-section {
             width: 100%;
             margin: 0 auto;
             padding: 20px;
             box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
             background-color: #fff;
         }

         .resume-guide-section .row,
         .resume-content-section .row {
             display: flex;
             align-items: center;
             margin-bottom: 10px;
             border: 1px solid #d3c8c8;
             padding: 10px;
             border-radius: 5px;
             position: relative;
         }

         .resume-guide-section input.form-control .resume-content-section input.form-control {
             border: 1px solid #cccccc47;
             cursor: unset;
         }

         .col1 {
             flex: 1;
             display: flex;
             flex-direction: column;
             margin-right: 10px;
         }

         .col1 input {
             padding: 10px;
             margin-bottom: 10px;
             border: 1px solid #ccc;
             border-radius: 4px;
         }

         .col2 {
             margin-right: 10px;
             display: inline-flex;
             gap: 10px;
         }

         .col2 img {
             width: 60px;
             height: 60px;
             display: block;
         }

         .btn-delete,
         .btn-resume-content-delete {
             padding: 10px 15px;
             background-color: #ff4d4d;
             color: #fff;
             border: none;
             border-radius: 4px;
             cursor: pointer;
             flex-shrink: 0;
             text-decoration: auto;
             text-align: center;
         }

         .btn-delete:hover,
         .btn-resume-content-delete:hover {
             background-color: #ff0000;
         }

         .resume-guide-section textarea.form-control,
         .resume-content-section textarea.form-control {
             margin-bottom: 16px;
             height: auto;
             min-height: 120px;
             overflow-y: auto;
         }

         .resume-guide-section .form-control[readonly],
         .resume-content-section .form-control[readonly] {
             background-color: unset;
         }

         .resume-guide-section .btn-edit,
         .resume-content-section .btn-resume-content-edit {
             width: 99px;
             float: right;
         }

         .btn-edit,
         .btn-resume-content-edit {
             padding: 10px 15px;
             background-color: #0d7739f1;
             color: #fff;
             border: none;
             border-radius: 4px;
             cursor: pointer;
             flex-shrink: 0;
             text-decoration: auto;
             text-align: center;
         }

         .btn-edit:hover,
         .btn-resume-content-edit:hover {
             background-color: #22e975;
         }

         textarea#descriptionResumeContent {
             height: 180px;
         }

         .button-footer {
             display: flex;
             text-align: right;
             position: absolute;
             right: 25px;
             gap: 5px;
             bottom: 21px;
         }

         .footer-button-sticky {
             position: sticky;
             bottom: 5px;
             /* border: 2px solid #bdbdbd; */
             background: #fff;
             padding: 0;
         }

         .bootstrap-tagsinput .tag {
             color: #FFF;
             background-color: #1f6bcd;
             padding: 3px 16px;
             border-radius: 13px;
             font-size: 14px;
         }

         .bootstrap-tagsinput {
             width: 100%;
         }

         .bootstrap-tagsinput input {
             padding: 0.375rem 0.75rem !important;
         }

         .bootstrap-tagsinput .tag [data-role="remove"] {
             margin-left: 8px;
             cursor: pointer;
             font-weight: 600;
         }

         .fa-star,
         .fa-star-half-alt {
             font-size: 16px;
             color: #ccc;
         }

         .fa-star.checked,
         .fa-star-half-alt.checked {
             color: #ffcc00;
         }

         div#DataTables_Table_0_paginate {
             float: right;
             margin-right: 5px;
         }

         .custom-modal-width {
             max-width: 700px !important;
             width: 100% !important;
         }


         h6 {
             display: inline-block;
             margin-right: 5px;
         }

         .svg-icon {
             width: 16px;
             height: 16px;
             fill: currentColor;
         }

         .sort-arrow {
             margin-left: 0px;
         }

         .sort-arrow.active {
             color: #1739d1ec;
         }

         .sort-arrow {
             font-family: 'dropways';
             font-size: 14px;
             color: grey;
             /* Default color */
         }

         .sort-asc::before {
             content: "\eabb";
             /* Custom icon content for up arrow */
         }

         .sort-desc::before {
             content: "\eaba";
         }

         /* Active state with blue color */
         .sort-asc.active::before,
         .sort-desc.active::before {
             color: #1b00ff;
         }

         span.sort-arrow.sort-desc {
             margin-left: -7px;
         }


         .pd-20.f-left {
             float: left;
         }



         .row ul.pagination {
             margin-bottom: 10px;
             float: right;
             margin-right: 20px;
         }

         .sort-arrow {
             margin-left: 0px;
         }

         .sort-arrow.active {
             color: #1739d1ec;
         }

         .sort-arrow {
             font-family: 'dropways';
             font-size: 14px;
             color: grey;
             /* Default color */
         }

         .sort-asc::before {
             content: "\eabb";
             /* Custom icon content for up arrow */
         }

         .sort-desc::before {
             content: "\eaba";
         }

         /* Active state with blue color */
         .sort-asc.active::before,
         .sort-desc.active::before {
             color: #1b00ff;
         }

         span.sort-arrow.sort-desc {
             margin-left: -7px;
         }


         .pd-20.f-left {
             float: left;
         }



         .row ul.pagination {
             margin-bottom: 10px;
             float: right;
             margin-right: 20px;
         }

         .select2.select2-container.select2-container--default {
             width: 100% !important;
         }

         .accordion-menu ul {
             padding: 0px !important;
         }

         .value-container {
             width: 100%;
         }

         .dropdown-container {
             width: 100%;
         }

         .value-dropdown {
             width: 100% !important;
         }

         /* @media (min-width: 768px) {
            .datacolor {
                -ms-flex: 0 0 33.333333%;
                -webkit-box-flex: 0;
                flex: 0 0 33.333333%;
                max-width: none !important;
            }
        } */

         /* Compact and inline pagination styling */
         .dataTables_paginate.paging_simple_numbers {
             overflow: hidden;
             display: inline-block;
             align-items: center;
             height: 24px;
         }

         .dataTables_paginate .pagination {
             margin: 0;
             font-size: 13px;
             height: 28px;
             flex-wrap: nowrap;
         }

         .dataTables_paginate .page-item .page-link {
             padding: 2px 6px;
             height: 24px;
             line-height: 20px;
             font-size: 13px;
         }

         /* Info text styling */
         .dataTables_info {
             font-size: 13px !important;
             margin-left: 10px !important;
             padding-right: 10px;
         }

         /* Responsive: force inline and center on small screens */
         @media (max-width: 768px) {

             .dataTables_paginate,
             .dataTables_info {
                 justify-content: center;
                 text-align: center;
                 width: 100%;
             }

             .dataTables_paginate .pagination {
                 flex-wrap: wrap;
                 justify-content: center;
             }
         }

         .item-form-input {
             height: 35px !important;
             line-height: 10px !important;
         }

         /* .form-input-width{
            width: 150px !important;
         } */

         /* small styling for sub-feature line + hover feel */
         .sub-feature-line {
             background: #fafafa;
             border: 1px solid #eee;
             box-shadow: 0 1px 0 rgba(0, 0, 0, 0.02);
         }

         .feature-row.selected {
             background-color: #b2eaff;
         }

         .sub-row .sub-feature-line:hover {
             transform: translateY(-1px);
             transition: all 0.12s ease;
         }

         .info-icon {
             margin-left: 10px;
             color: #007bff;
             cursor: pointer;
             font-size: 15px;
             position: relative;
         }

         .hover-note {
             display: none;
             position: absolute;
             top: 60px;
             left: 0px;
             background: #fff;
             border: 1px solid #ccc;
             padding: 6px 10px;
             border-radius: 5px;
             box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
             font-size: 12px;
             white-space: pre-wrap;
             max-width: 400px;
             min-width: 80px;
             z-index: 100;
             word-break: break-word;
         }

         .info-icon:hover+.hover-note {
             display: block;
         }

         /* Meta-like Filter Styles */
         .meta-filter-container {
             background: #f8f9fa;
             border-radius: 6px;
             padding: 12px;
             border: 1px solid #e9ecef;
             position: relative;
         }

         .meta-filter-row {
             display: flex;
             align-items: center;
             flex-wrap: wrap;
             gap: 8px;
         }

         .meta-filter-group {
             display: flex;
             align-items: center;
             background: white;
             border: 1px solid #ddd;
             border-radius: 4px;
             padding: 4px 8px;
             margin-right: 8px;
         }

         .meta-filter-label {
             font-size: 12px;
             font-weight: 600;
             color: #65676b;
             margin-right: 8px;
             white-space: nowrap;
         }

         .meta-filter-btn {
             background: none;
             border: none;
             padding: 4px 10px;
             font-size: 12px;
             border-radius: 3px;
             cursor: pointer;
             margin: 0 2px;
             transition: all 0.2s;
         }

         .meta-filter-btn.active {
             background: #1877f2;
             color: white;
         }

         .meta-filter-btn:not(.active) {
             background: #f0f2f5;
             color: #65676b;
         }

         .meta-filter-btn:not(.active):hover {
             background: #e4e6eb;
         }

         .meta-active-filters {
             background: white;
             border-radius: 4px;
             padding: 8px 12px;
             margin-bottom: -5px;
             border: 1px solid #e9ecef;
             font-size: 13px;
         }

         .filter-pill {
             display: inline-flex;
             align-items: center;
             background: #e7f3ff;
             color: #1877f2;
             padding: 2px 8px;
             border-radius: 12px;
             font-size: 11px;
             margin-right: 6px;
             margin-bottom: 4px;
         }

         .filter-pill-remove {
             margin-left: 4px;
             cursor: pointer;
             font-weight: bold;
         }

         /* Improved Search Bar Styles */
         .search-container {
             position: absolute;
             top: 12px;
             right: 15px;
             z-index: 1000;
         }

         .search-box {
             display: flex;
             align-items: center;
             background: white;
             border: 1px solid #d1d5db;
             border-radius: 6px;
             padding: 3px 10px;
             box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
             width: 280px;
             transition: all 0.2s ease;
         }

         .search-box:focus-within {
             border-color: #1877f2;
             box-shadow: 0 0 0 2px rgba(24, 119, 242, 0.1);
         }

         .search-input {
             border: none;
             outline: none;
             flex: 1;
             padding: 4px 12px;
             font-size: 13px;
             background: transparent;
             color: #1f2937;
         }

         .search-input::placeholder {
             color: #9ca3af;
         }

         .search-icon {
             color: #6b7280;
             margin-right: 6px;
             font-size: 14px;
         }

         .clear-search {
             background: none;
             border: none;
             color: #6b7280;
             cursor: pointer;
             padding: 2px;
             font-size: 16px;
             width: 20px;
             height: 20px;
             display: flex;
             align-items: center;
             justify-content: center;
             border-radius: 50%;
             transition: all 0.2s ease;
             opacity: 0;
             visibility: hidden;
         }

         .clear-search.show {
             opacity: 1;
             visibility: visible;
         }

         .clear-search:hover {
             background: #f3f4f6;
             color: #374151;
         }

         /* Header row styling */
         .header-row {
             display: flex;
             justify-content: space-between;
             align-items: flex-start;
             margin-bottom: 15px;
             gap: 15px;
         }

         .filters-section {
             flex: 1;
         }
     </style>
 </head>

 <body class="antialiased">

     @include('layouts.header')</body>
 <div id="main_loading_screen" style="display: block;">
     <div id="loader-wrapper">
         <div id="loader"></div>
         <div class="loader-section section-left"></div>
         <div class="loader-section section-right"></div>
     </div>
 </div>

 </html>