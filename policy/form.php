<form method="POST" name="policies-form" id="policies-form" class="p-3" enctype="multipart/form-data">
    <div class="card shadow-sm p-3 rounded">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" required minlength="2"
                    value="<?php echo isset($row['name']) ? $row['name'] : ''; ?>">
            </div>

            <div class="col-md-6 mb-3">
                <label for="file" class="form-label">Document</label>
                <input type="file" class="form-control" id="file" name="file[]" <?php echo isset($row['file']) ? '' : 'required'; ?> multiple
                    accept="image/*, .doc, .docx, .txt, .pdf, .mp4, .avi, .mov">
                <small class="text-muted">Allowed file types: Images, DOC, TXT, PDF</small>
            </div>
        </div>

        <div class="row">
            <div class="col-md-10 mb-3">
                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                <textarea class="form-control" name="description" id="description" rows="6"><?php echo isset($row['description']) ? $row['description'] : ''; ?></textarea>
            </div>

            <?php if (isset($row['file']) && !empty($row['file'])): ?>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Uploaded Files</label>
                    <ul class="list-unstyled border rounded p-2" style="max-height: 270px; overflow-y: auto;">
                        <?php
                        $files = is_array($row['file']) ? $row['file'] : explode(',', $row['file']);
                        $base_url = '/uploads/';
                        $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        foreach ($files as $file):
                            $file = trim($file);
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        ?>
                            <li class="d-flex justify-content-between align-items-center mb-2">
                                <div class="me-2">
                                    <?php if (in_array($ext, $image_extensions)): ?>
                                        <a href="<?php echo  htmlspecialchars($file); ?>" target="_blank">
                                            <img src="<?php echo htmlspecialchars($file); ?>" alt="File" class="img-thumbnail" style="max-height: 100px;">
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo BASE_URL . '/' . htmlspecialchars($file); ?>" target="_blank">
                                            <?php echo htmlspecialchars(basename($file)); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-file-btn" data-file="<?php echo htmlspecialchars($file); ?>" title="Remove file">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <input type="hidden" name="id" value="<?php echo isset($row['id']) ? $row['id'] : ''; ?>">

        <div class="text-end">
            <button type="submit" class="btn btn-primary" name="<?php echo isset($row['id']) ? 'edit-policies' : 'add_policies'; ?>">
                <?php echo isset($row['id']) ? 'Update' : 'Submit'; ?>
            </button>
        </div>
    </div>
</form>

<script>
    $(document).ready(function() {
        $('#description').summernote({
            height: 200
        });

        $('#policies-form').validate({
            ignore: [],
            rules: {
                name: "required",
                <?php if (!isset($row['file'])): ?>
                    file: "required",
                <?php endif; ?>
            },
            messages: {
                name: "Please enter policy name",
                file: "Please select at least one file"
            },
            errorPlacement: function(error, element) {
                error.addClass("invalid-feedback");
                if (element.hasClass("form-select")) {
                    error.insertAfter(element.next('.select2'));
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function(element) {
                $(element).addClass("is-invalid");
                if ($(element).hasClass("form-select")) {
                    $(element).next('.select2').find('.select2-selection').addClass('is-invalid');
                }
            },
            unhighlight: function(element) {
                $(element).removeClass("is-invalid");
                if ($(element).hasClass("form-select")) {
                    $(element).next('.select2').find('.select2-selection').removeClass('is-invalid');
                }
            }
        });

        // Handle file removal
        document.querySelectorAll('.remove-file-btn').forEach(button => {
            button.addEventListener('click', function() {
                const file = this.getAttribute('data-file');
                this.closest('li').remove();
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'remove_files[]';
                input.value = file;
                document.getElementById('policies-form').appendChild(input);
            });
        });
    });
</script>