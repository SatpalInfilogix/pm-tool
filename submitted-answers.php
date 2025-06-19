<?php
require_once './includes/header.php';
require_once 'includes/db.php';

// Fetch submissions
$sql = "SELECT * FROM test_submissions ORDER BY submitted_at DESC";
$result = $conn->query($sql);

$submissions = [];
while ($row = $result->fetch_assoc()) {
    // Decode JSON answers column
    $answersDecoded = json_decode($row['answers'], true);
    $row['answers'] = is_array($answersDecoded) ? $answersDecoded : [];
    $submissions[] = $row;
}

?>

<div class="container py-4">
    <h2 class="mb-4 text-primary">📝 Submitted Test Answers</h2>

    <?php if (empty($submissions)): ?>
        <div class="alert alert-warning">No submissions found.</div>
    <?php else: ?>
        <?php foreach ($submissions as $index => $submission): ?>
            <?php $collapseId = "collapse-" . $submission['id']; ?>
            <div class="card mb-3 shadow-sm">
                <div
                    class="card-header bg-light text-dark d-flex justify-content-between align-items-center cursor-pointer"
                    data-bs-toggle="collapse"
                    data-bs-target="#<?= $collapseId ?>"
                    aria-expanded="false"
                    aria-controls="<?= $collapseId ?>"
                    style="cursor: pointer;">
                    <div>
                        <strong><?= htmlspecialchars($submission['name']) ?></strong><br>
                        <small><?= htmlspecialchars($submission['email']) ?> | <?= htmlspecialchars($submission['phone']) ?></small>
                    </div>
                    <div>
                        <small><?= date("d M Y, h:i A", strtotime($submission['submitted_at'])) ?></small>
                    </div>
                </div>

                <div class="collapse" id="<?= $collapseId ?>">
                    <div class="card-body">
                        <?php
                        $i = 1;
                        foreach ($submission['answers'] as $key => $answer) {
                            $qid = intval(substr($key, 1));
                            $qResult = $conn->query("SELECT question FROM interview_questions WHERE id = $qid");

                            $qText = ($qResult && $qResult->num_rows > 0)
                                ? $qResult->fetch_assoc()['question']
                                : "❌ Question not found (ID: $qid)";

                            echo "<p><strong>$i. " . htmlspecialchars($qText) . "</strong><br>";
                            echo "<em>" . nl2br(htmlspecialchars($answer)) . "</em></p>";
                            $i++;
                        }

                        if (empty($submission['answers'])) {
                            echo "<div class='text-danger'>⚠️ No answers submitted.</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once './includes/footer.php'; ?>