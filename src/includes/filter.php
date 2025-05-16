<?php
// $tour_name, $tags, $tag_counts, $conn được truyền vào từ tour.php
?>

<div class="w-full max-w-sm bg-white rounded-lg shadow p-4 sticky top-10">

  <!-- Support box -->
  <div class="flex items-center gap-3 mb-6 border rounded-md p-3 shadow-sm">
    <div class="p-3 bg-gray-100 rounded-full">
      <i class="fas fa-headset text-3xl text-black"></i>
    </div>
    <div>
      <h6 class="font-semibold">Cần hỗ trợ?</h6>
      <div class="text-sm">HD <a href="tel:0948773012" class="text-orange-500 font-semibold hover:underline">0948773012</a></div>
    </div>
  </div>

  <!-- Filter form -->
  <form method="GET" class="space-y-5">

    <!-- Search tour name -->
    <div>
      <input
        type="text"
        name="tour_name"
        placeholder="Nhập tên tour"
        value="<?= htmlspecialchars($tour_name) ?>"
        class="w-full rounded border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-400"
      />
      <button type="submit" class="mt-2 w-12 h-10 bg-yellow-400 text-white rounded float-right flex items-center justify-center hover:bg-yellow-500 transition">
        <i class="fas fa-search"></i>
      </button>
    </div>

    <hr class="my-4 border-gray-300">

    <!-- Tags filter -->
    <div>
      <h5 class="mb-3 font-semibold text-gray-700">Tags</h5>
      <?php foreach ($tag_counts as $tag => $count):
        $checked = in_array($tag, $tags) ? 'checked' : '';
      ?>
      <label class="inline-flex items-center mb-2 cursor-pointer w-full">
        <input type="checkbox" name="tags[]" value="<?= htmlspecialchars($tag) ?>" class="form-checkbox" <?= $checked ?> />
        <span class="ml-2 flex justify-between w-full">
          <span><?= htmlspecialchars($tag) ?></span>
          <span class="text-gray-500">(<?= $count ?>)</span>
        </span>
      </label>
      <?php endforeach; ?>
    </div>

    <button type="submit" class="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded transition">
      Lọc
    </button>
  </form>
</div>
