<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$categories = get_categories($conn);
$business_profile = get_business_profile($conn, $_SESSION['user_id']);

// Function to check if the user has an active subscription
function has_active_subscription($conn, $user_id) {
    // Check for an approved subscription and valid payment
    $stmt = $conn->prepare("
        SELECT s.subscription_status, p.payment_status, u.status
        FROM subscriptions s
        LEFT JOIN payments p ON s.subscription_id = p.subscription_id
        JOIN users u ON s.user_id = u.user_id
        WHERE s.user_id = ? 
        AND s.subscription_status = 'approved'
        AND (p.payment_status = 'paid' OR p.payment_status IS NULL)
        AND u.status = 'active'
        ORDER BY s.created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result !== false; // Return true if a valid subscription exists
}

$is_subscribed = has_active_subscription($conn, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Post - BizShowcase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/add-post.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="form-section">
            <div class="form-title">Create New Post</div>
            <form id="postForm" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Company Name</label>
                        <input type="text" class="form-control" name="company_name" value="<?php echo htmlspecialchars($business_profile['company_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Business Email</label>
                        <input type="email" class="form-control" name="business_email" value="<?php echo htmlspecialchars($business_profile['business_email'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Upload Images</label>
                        <input type="file" class="form-control" name="images[]" multiple accept="image/*">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Business Number</label>
                        <input type="text" class="form-control" name="business_number" value="<?php echo htmlspecialchars($business_profile['business_number'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Seller Type</label>
                        <select class="form-select shadow-sm" name="seller_type" required>
                            <option value="" <?php echo (isset($business_profile['seller_type']) && $business_profile['seller_type'] === '') ? 'selected' : ''; ?>>Select Seller Type</option>
                            <option value="Sole Entrepreneurship" <?php echo (isset($business_profile['seller_type']) && $business_profile['seller_type'] === 'Sole Entrepreneurship') ? 'selected' : ''; ?>>Sole Entrepreneurship</option>
                            <option value="Partnership" <?php echo (isset($business_profile['seller_type']) && $business_profile['seller_type'] === 'Partnership') ? 'selected' : ''; ?>>Partnership</option>
                            <option value="Cooperation" <?php echo (isset($business_profile['seller_type']) && $business_profile['seller_type'] === 'Cooperation') ? 'selected' : ''; ?>>Cooperation</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Business Address</label>
                        <input type="text" class="form-control" name="business_address" value="<?php echo htmlspecialchars($business_profile['business_address'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select class="form-select shadow-sm" name="category_id" id="categorySelect" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="request_new">Request New Category</option>
                        </select>
                        <input type="text" class="form-control mt-2 d-none" id="newCategoryInput" name="new_category" placeholder="Enter new category name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="6" placeholder="Description" style="font-family: 'Poppins', sans-serif; border-color: #D9D9D9; resize: vertical;" maxlength="1000" required></textarea>
                    </div>
                    <div class="text-end mt-4">
                        <button type="button" class="submit-btn" data-bs-toggle="modal" data-bs-target="#previewModal">Preview</button>
                        <button type="submit" class="submit-btn">Post</button>
                        <button type="button" class="cancel-btn" onclick="cancelChanges()">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Post Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h5 id="previewCompany"></h5>
                    <p id="previewDescription"></p>
                    <div id="previewImages"></div>
                    <p><strong>Email:</strong> <span id="previewEmail"></span></p>
                    <p><strong>Address:</strong> <span id="previewAddress"></span></p>
                    <p><strong>Number:</strong> <span id="previewNumber"></span></p>
                    <p><strong>Seller Type:</strong> <span id="previewSellerType"></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription Warning Modal -->
    <div class="modal fade" id="subscriptionWarningModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Subscription Required</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You need an active subscription to post. Please subscribe to continue.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="subscription.php" class="btn btn-primary">Go to Subscription</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Show/hide new category input
            $('#categorySelect').change(function() {
                if ($(this).val() === 'request_new') {
                    $('#newCategoryInput').removeClass('d-none');
                } else {
                    $('#newCategoryInput').addClass('d-none');
                }
            });

            // Preview modal
            $('#previewModal').on('show.bs.modal', function() {
                $('#previewCompany').text($('input[name="company_name"]').val());
                $('#previewDescription').text($('textarea[name="description"]').val());
                $('#previewEmail').text($('input[name="business_email"]').val());
                $('#previewAddress').text($('input[name="business_address"]').val());
                $('#previewNumber').text($('input[name="business_number"]').val());
                $('#previewSellerType').text($('select[name="seller_type"]').val());
                
                let images = $('input[name="images[]"]')[0].files;
                let imagesHtml = '';
                for (let i = 0; i < images.length; i++) {
                    imagesHtml += `<img src="${URL.createObjectURL(images[i])}" style="max-width:100px; margin-right:10px;">`;
                }
                $('#previewImages').html(imagesHtml);
            });

            // Submit post with subscription check
            $('#postForm').submit(function(e) {
                e.preventDefault();
                let isSubscribed = <?php echo json_encode($is_subscribed); ?>;

                if (!isSubscribed) {
                    $('#subscriptionWarningModal').modal('show');
                } else {
                    let formData = new FormData(this);
                    formData.append('action', 'add_post');
                    
                    $.ajax({
                        url: '../ajax/user_actions.php',
                        method: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            alert('Post created successfully!');
                            window.location.href = 'home.php';
                        },
                        error: function(xhr) {
                            alert('Error: ' + xhr.responseText);
                        }
                    });
                }
            });

            // Cancel changes
            window.cancelChanges = function() {
                if (confirm("Are you sure you want to cancel changes?")) {
                    window.location.href = 'home.php';
                }
            };

            // Sidebar toggle
            window.toggleSidebar = function() {
                document.getElementById('sidebar').classList.toggle('collapsed');
            };

            // Auto-collapse sidebar on page load
            window.onload = function() {
                toggleSidebar();
            };
        });
    </script>
</body>
</html>