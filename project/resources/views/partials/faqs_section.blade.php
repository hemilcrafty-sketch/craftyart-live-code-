<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="{{asset('assets/vendors/styles/core.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('assets/vendors/styles/icon-font.min.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('assets/vendors/styles/style.css')}}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css">
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/custom.css')}}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
  crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
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
</style>
<div class="modal fade" id="faqsModel" tabindex="-1" aria-labelledby="faqsModel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title text-capitalize content-title">Faqs</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="closeFaqModel()"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <label for="question" class="form-label">Question</label>
            <div>
              <textarea style="height: 150px;" id="question"></textarea>
            </div>
            <span class="question_error text-danger"></span>
          </div>
          <div class="col-md-12 mb-5">
            <label for="answer" class="form-label">Answer</label>
            <div id="answer"></div>
            <span class="answer_error text-danger"></span>
          </div>
        </div>
      </div>
      <div class="modal-footer mt-5">
        <button class="btn btn-dark" id="btn-faqs-save" onclick="saveFaq(event)">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="linkModal" tabindex="-1" role="dialog" aria-labelledby="linkModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="linkModalLabel">Insert Link</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="form-group mb-3">
          <label for="linkUrl">URL</label>
          <input type="text" class="form-control" id="linkUrl">
          <span class="linkUrlError text-danger"></span>
        </div>
        <div class="form-check">
          <input type="checkbox" class="form-check-input" id="openInNewTab">
          <label class="form-check-label" for="openInNewTab">Open in new tab</label>
        </div>
        <div class="form-check">
          <input type="checkbox" class="form-check-input" id="nofollow">
          <label class="form-check-label" for="nofollow">Add rel="nofollow"</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="insertLink">Insert</button>
      </div>
    </div>
  </div>
</div>

<div class="rounded-3"  style="border: dashed; padding: 10px;">
    <div class="mb-3 w-100">
        <label for="faqs_title" class="form-label">Faqs Title</label>
        <input type="text" class="form-control" id="faqs_title"
               name="faqs_title" placeholder="Enter Faqs title"
        >
    </div>
    <div class="content-div">
        <input type="hidden" name="faqs" id="faqs" value="{{ $faqs ?? old('faqs')}}">
        <div class="faqs-content col-md-12"></div>
    </div>
    <button type="button" class="btn btn-dark mb-3 w-100" onclick="openFaqModal()">Add
        Faqs</button>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script type="text/javascript">
  var answer;
  var faqs = [];
  var faqSection = @json($faqs);


  var removeStyleRegex = /(<a[^>]*)\s*style="[^"]*"/gi;

  document.addEventListener('DOMContentLoaded', function () {
    $('#faqsModel').modal({
      backdrop: 'static',
      keyboard: false
    });
    if (faqSection && typeof faqSection === "string") {
      faqSection = JSON.parse(faqSection);
    }
    if (typeof faqSection == "object") {
        if(Array.isArray(faqSection)){
            faqSection.forEach(function (item) {
                faqs.push({
                    'question': item.question,
                    'answer': item.answer
                });
                if (item.answer) {
                    item.answer = item.answer.replace(removeStyleRegex, '$1'); // Remove style attribute from <a> tags
                }
            });
            callFaqs(faqSection);
        } else {
            document.getElementById("faqs_title").value = faqSection['title']
            faqSection['faqs'].forEach(function (item) {
                faqs.push({
                    'question': item.question,
                    'answer': item.answer
                });
                if (item.answer) {
                    item.answer = item.answer.replace(removeStyleRegex, '$1'); // Remove style attribute from <a> tags
                }
            });
            callFaqs(faqSection['faqs']);
        }
    }
  });

  document.addEventListener("DOMContentLoaded", function () {
    let Link = Quill.import('formats/link');
    class CustomLink extends Link {
      static create(value) {
        let node = super.create(value);
        if (value && value.target) {
          node.setAttribute('href', value.href);
          if (value.target == '_self' || value.target == '_blank') {
            node.setAttribute('target', value.target);
          } else {
            node.setAttribute('target', '_blank');
          }
          if (value.nofollow) {
            node.setAttribute('rel', 'nofollow');
          } else {
            node.setAttribute('rel', 'dofollow');
          }
          return node;
        } else {
          node.setAttribute('href', value);
          return node;
        }
      }
    }
    Quill.register('formats/link', CustomLink, true);
    let toolbarOptions = {
      container: [
        ["undo", "redo"],
        ["bold", "italic"],
        [{
          'color': []
        }],
        ["custom"],
        [{
          'link': 'link'
        }], // Use 'link' as the identifier for the link button
        [{
          'target': '_self'
        }, {
          'target': '_blank'
        }] // Target dropdown menu
      ],
      handlers: {
        'custom': function(value){
          colorPickerCustomQuill(value,this.quill,"faqsModel")
        },
        'link': function (value) {
          if (value) {
            $(".linkUrlError").text('');
            $('#linkModal').modal('show');
            $('#linkUrl').val('');
            $('#openInNewTab').prop('checked', false);
            $('#nofollow').prop('checked', false);

            // Define the event listener function
            var insertLinkHandler = function () {
              insertLink(this.quill);
              // Remove the event listener after execution
              document.getElementById('insertLink').removeEventListener('click', insertLinkHandler);
            }.bind(this);
            document.getElementById('insertLink').addEventListener('click', insertLinkHandler);
          }
        },
        'target': function (value) {
          this.quill.format('link', {
            target: value
          });
        }
      }
    };

    answer = new Quill("#answer", {
      theme: "snow",
      modules: {
        toolbar: toolbarOptions,
        history: {
          userOnly: true,
          maxStack: 500
        },
      },
    });
    answer.on('text-change', function (delta, oldDelta, source) {
      if (source === 'user') {
        formChanged = true;
        // User made changes
      }
    });

    // Update undo and redo icons
    $(".ql-undo").html('<i class="fa-solid fa-rotate-left"></i>');
    $(".ql-redo").html('<i class="fa-solid fa-rotate-right"></i>');
  });

  function insertLink(quillInstance) {
    var href = $('#linkUrl').val();
    var openInNewTab = $('#openInNewTab').prop('checked');
    var noFollow = $('#nofollow').prop('checked');
    var target = openInNewTab ? '_blank' : '_self';

    if (href) {

      $(".linkUrlError").text('');
      sessionStorage.setItem("href", href);
      quillInstance.format('link', {
        href: href,
        target: target,
        nofollow: noFollow
      });

      // Close the modal
      $('#linkModal').modal('hide');
    } else {
      var href = $('.linkUrlError').text('Please Add Link Here');
    }
  }

  function openFaqModal() {
    $("#faqsModel").modal("show");
  }

  function closeFaqModel() {
    $("#btn-faqs-save").attr('data-type', '');
    $("#btn-faqs-save").attr('data-index', 0);

    answer.root.innerHTML = '';
    $("#question").val('');
    $("#faqsModel").modal("hide");
  }

  function saveFaq(event) {
    // Prevent form submission
    if (event) event.preventDefault();

    let questionInput = $("#question").val();
    if (answer.getText().trim() == '') $('.answer_error').text('Please fill this field.');
    else $('.answer_error').text('');

    if (questionInput.trim() == '') $('.question_error').text('Please fill this field.');
    else $('.question_error').text('');

    if (questionInput.trim() == '' || answer.getText().trim() == '') return;

    if ($("#btn-faqs-save").attr("data-type") == 'edit') {
      var dataId = $("#btn-faqs-save").attr("data-index");
      if (dataId == "" || dataId == undefined) {
        var index = faqs.length;
        faqs[index] = [];
      } else {
        var index = dataId;
      }

      faqs[index] = {
        'question': questionInput,
        'answer': answer.root.innerHTML
      };
    } else {
      faqs.push({
        'question': questionInput,
        'answer': answer.root.innerHTML
      });
    }
    callFaqs(faqs);

    $("#btn-faqs-save").attr('data-type', '');
    $("#btn-faqs-save").attr('data-index', 0);

    answer.root.innerHTML = '';
    $("#question").val('');

    $(".btn-close").click();

  }

  function callFaqs(faqs) {
    var data = "";
    faqs.forEach((ele, index) => {
      data += `<div class="mb-3 p-2 border border-2 sortable_faqs_list content_show_${index}" data-id="${index}"><div class="drag-handle">&#9776;</div>
                <div class="col-12 mb-2 py-2 px-3">
                    <div class="row">
                        <div class="col-md-2">Question : </div>
                        <div class="col-md-10">${ele.question}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">Answer : </div>
                        <div class="col-md-10">${ele.answer}</div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-success" onclick="editFaq(${index})">Edit Faq</button>
                    <button type="button" class="btn btn-danger" onclick="deleteFaq(${index})">Remove Faq</button>
                </div>
            </div>`;
    });
    $(".faqs-content").html(data);
    $("#faqs").val(JSON.stringify(faqs));
    initializeFaqs();
  }

  function initializeFaqs() {
    $(".faqs-content").sortable({
      items: ".sortable_faqs_list", // selector for sortable items
      handle: ".drag-handle", // selector for the handle
      update: function (event, ui) {
        var newOrder = [];
        $(".faqs-content .sortable_faqs_list").each(function (index) {
          newOrder.push(faqs[$(this).data("id")]);
        });
        faqs = newOrder;
        callFaqs(faqs);
      },
    });
  }

  function editFaq(index) {
    answer.root.innerHTML = faqs[index].answer;
    $("#question").val(faqs[index].question);
    $('#btn-faqs-save').attr('data-type', 'edit');
    $('#btn-faqs-save').attr('data-index', index);
    $("#faqsModel").modal("show");
  }

  function deleteFaq(index) {
    Swal.fire({
      title: "Are you sure?",
      text: "You want to delete this faq?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, delete it!"
    }).then((result) => {
      if (result.isConfirmed) {
        faqs.splice(index, 1);
        callFaqs(faqs);
      }
    });
  }

  $(document).on('shown.modal', '#faqsModel', function () {
    $('.question_error').text('');
    $('.answer_error').text('');
  })

  $(document).on('hide.modal', '#faqsModel', function () {
    $('#btn-faqs-save').attr('data-type', '');
    $('#btn-faqs-save').attr('data-index', 0);

    // question.root.innerHTML = '';
    answer.root.innerHTML = '';
    $("#question").val('');
  });
</script>