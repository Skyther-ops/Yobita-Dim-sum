<?php 
  session_start();
  require_once '../connection.php';
  
  // Check if user is logged in
  if (!isset($_SESSION['user_id'])) {
      header("Location: ../index.php");
      exit;
  }
  
  // Check if table is selected
  if (!isset($_SESSION['selected_table_id'])) {
      header("Location: ../table/table.php");
      exit;
  }
  
  $pageTitle = "Menu"; 
  $basePath = "../";
  include '../_header.php'; 

  // --- MOCK DATABASE ---
  // In your real app, this array will come from your MySQL database
  $menuItems = [
    [
      'id' => 1,
      'name' => 'Xiao Long Bao',
      'description' => 'Classic soup dumplings',
      'price' => 12.50,
      'image' => 'https://via.placeholder.com/150.png?text=Item+1',
      'category' => 'Dumplings'
    ],
    [
      'id' => 2,
      'name' => 'Siu Mai',
      'description' => 'Pork and shrimp',
      'price' => 10.00,
      'image' => 'https://via.placeholder.com/150.png?text=Item+2',
      'category' => 'Dumplings'
    ],
    [
      'id' => 3,
      'name' => 'Dan Dan Noodles',
      'description' => 'Spicy Szechuan noodles',
      'price' => 15.00,
      'image' => 'https://via.placeholder.com/150.png?text=Item+3',
      'category' => 'Noodles'
    ],
    [
      'id' => 4,
      'name' => 'Cheung Fun',
      'description' => 'Rice noodle roll',
      'price' => 9.50,
      'image' => 'https://via.placeholder.com/150.png?text=Item+4',
      'category' => 'Appetizers'
    ]
  ];
?>

<main class="main-wrapper">

  <div class="app-container">
    <div style="text-align: center; margin-bottom: 20px;">
      <span style="background-color: var(--fourth); padding: 8px 16px; border-radius: 20px; font-weight: 600; color: var(--text-dark);">
        Table: <?php echo htmlspecialchars($_SESSION['selected_table_number']); ?>
      </span>
    </div>
    <h2 class="page-title">Our Menu</h2>

    <div class="category-filters">
      <button class="filter-btn active" data-filter="all">All</button>
      <button class="filter-btn" data-filter="Dumplings">Dumplings</button>
      <button class="filter-btn" data-filter="Noodles">Noodles</button>
      <button class="filter-btn" data-filter="Appetizers">Appetizers</button>
    </div>

    <div id="menu-grid">
      
      <?php foreach ($menuItems as $item): ?>
        <article class="menu-item-card" data-category="<?php echo htmlspecialchars($item['category']); ?>">
          
          <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
          
          <div class="item-info">
            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
            <p><?php echo htmlspecialchars($item['description']); ?></p>
            
            <div class="item-footer">
              <span class="item-price">
                RM <?php echo number_format($item['price'], 2); ?>
              </span>
              
              <button class="add-to-cart-btn" 
                data-id="<?php echo $item['id']; ?>"
                data-name="<?php echo htmlspecialchars($item['name']); ?>"
                data-price="<?php echo $item['price']; ?>">
                +
              </button>
            </div>
          </div>
        </article>
      <?php endforeach; ?>

    </div>
  </div>

</main>