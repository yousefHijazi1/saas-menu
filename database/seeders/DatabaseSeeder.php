<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create a restaurant
        $restaurant = Restaurant::create([
            'name' => 'The Grand Bistro',
            'slug' => 'the-grand-bistro',
            'description' => 'A fine dining experience with exquisite cuisine from around the world.',
            'address' => '123 Main Street, Downtown',
            'phone' => '+1 (555) 123-4567',
            'email' => 'info@grandbistro.com',
            'currency' => '$',
            'is_active' => true,
        ]);

        // Create restaurant owner
        User::create([
            'name' => 'Restaurant Owner',
            'email' => 'restaurant@example.com',
            'password' => Hash::make('password'),
            'role' => 'restaurant',
            'restaurant_id' => $restaurant->id,
            'phone' => '+1 (555) 987-6543',
        ]);

        // Create waiter
        User::create([
            'name' => 'John Waiter',
            'email' => 'waiter@example.com',
            'password' => Hash::make('password'),
            'role' => 'waiter',
            'restaurant_id' => $restaurant->id,
            'phone' => '+1 (555) 456-7890',
        ]);

        // Create categories
        $appetizers = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Appetizers',
            'icon' => '🥗',
            'sort_order' => 1,
        ]);

        $mains = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Main Courses',
            'icon' => '🍖',
            'sort_order' => 2,
        ]);

        $pizza = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Pizza',
            'icon' => '🍕',
            'sort_order' => 3,
        ]);

        $drinks = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Beverages',
            'icon' => '🥤',
            'sort_order' => 4,
        ]);

        $desserts = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Desserts',
            'icon' => '🍰',
            'sort_order' => 5,
        ]);

        // Appetizers
        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $appetizers->id,
            'name' => 'Bruschetta',
            'description' => 'Toasted bread topped with fresh tomatoes, basil, and mozzarella',
            'price' => 8.99,
            'ingredients' => ['Bread', 'Tomatoes', 'Basil', 'Mozzarella', 'Olive Oil'],
            'is_available' => true,
            'sort_order' => 1,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $appetizers->id,
            'name' => 'Calamari Fritti',
            'description' => 'Crispy fried calamari served with marinara sauce',
            'price' => 12.99,
            'discount_price' => 10.99,
            'ingredients' => ['Calamari', 'Flour', 'Lemon', 'Marinara Sauce'],
            'is_available' => true,
            'is_featured' => true,
            'sort_order' => 2,
        ]);

        // Main Courses
        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $mains->id,
            'name' => 'Grilled Salmon',
            'description' => 'Atlantic salmon fillet with lemon butter sauce and seasonal vegetables',
            'price' => 24.99,
            'ingredients' => ['Salmon', 'Lemon', 'Butter', 'Vegetables', 'Herbs'],
            'is_available' => true,
            'is_featured' => true,
            'sort_order' => 1,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $mains->id,
            'name' => 'Ribeye Steak',
            'description' => '12oz prime ribeye with mashed potatoes and asparagus',
            'price' => 34.99,
            'ingredients' => ['Ribeye', 'Potatoes', 'Asparagus', 'Garlic Butter'],
            'is_available' => true,
            'sort_order' => 2,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $mains->id,
            'name' => 'Chicken Parmesan',
            'description' => 'Breaded chicken breast topped with marinara and melted cheese',
            'price' => 18.99,
            'ingredients' => ['Chicken', 'Mozzarella', 'Marinara', 'Parmesan'],
            'is_available' => true,
            'sort_order' => 3,
        ]);

        // Pizza
        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $pizza->id,
            'name' => 'Margherita Pizza',
            'description' => 'Classic pizza with tomato sauce, mozzarella, and fresh basil',
            'price' => 14.99,
            'ingredients' => ['Dough', 'Tomato Sauce', 'Mozzarella', 'Basil'],
            'is_available' => true,
            'sort_order' => 1,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $pizza->id,
            'name' => 'Pepperoni Pizza',
            'description' => 'Loaded with pepperoni and extra cheese',
            'price' => 16.99,
            'ingredients' => ['Dough', 'Tomato Sauce', 'Mozzarella', 'Pepperoni'],
            'is_available' => true,
            'is_featured' => true,
            'sort_order' => 2,
        ]);

        // Beverages
        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $drinks->id,
            'name' => 'Fresh Lemonade',
            'description' => 'House-made lemonade with fresh mint',
            'price' => 4.99,
            'ingredients' => ['Lemons', 'Sugar', 'Mint', 'Water'],
            'is_available' => true,
            'sort_order' => 1,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $drinks->id,
            'name' => 'Espresso',
            'description' => 'Double shot of our signature espresso blend',
            'price' => 3.99,
            'ingredients' => ['Coffee Beans'],
            'is_available' => true,
            'sort_order' => 2,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $drinks->id,
            'name' => 'Iced Tea',
            'description' => 'Refreshing brewed iced tea',
            'price' => 3.49,
            'ingredients' => ['Tea', 'Lemon', 'Ice'],
            'is_available' => true,
            'sort_order' => 3,
        ]);

        // Desserts
        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $desserts->id,
            'name' => 'Tiramisu',
            'description' => 'Classic Italian tiramisu with mascarpone cream',
            'price' => 9.99,
            'ingredients' => ['Mascarpone', 'Coffee', 'Ladyfingers', 'Cocoa'],
            'is_available' => true,
            'is_featured' => true,
            'sort_order' => 1,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $desserts->id,
            'name' => 'Chocolate Lava Cake',
            'description' => 'Warm chocolate cake with a molten center, served with vanilla ice cream',
            'price' => 11.99,
            'ingredients' => ['Chocolate', 'Butter', 'Eggs', 'Flour', 'Ice Cream'],
            'is_available' => true,
            'sort_order' => 2,
        ]);

        // Create tables
        for ($i = 1; $i <= 10; $i++) {
            Table::create([
                'restaurant_id' => $restaurant->id,
                'table_number' => (string) $i,
                'capacity' => $i <= 4 ? 2 : ($i <= 8 ? 4 : 6),
                'status' => 'available',
            ]);
        }

        // Second restaurant
        $restaurant2 = Restaurant::create([
            'name' => 'Coffee House',
            'slug' => 'coffee-house',
            'description' => 'Artisan coffee and pastries in a cozy atmosphere.',
            'address' => '456 Oak Avenue, Midtown',
            'phone' => '+1 (555) 234-5678',
            'email' => 'hello@coffeehouse.com',
            'currency' => '$',
            'is_active' => true,
        ]);

        $coffeeCat = Category::create([
            'restaurant_id' => $restaurant2->id,
            'name' => 'Coffee',
            'icon' => '☕',
            'sort_order' => 1,
        ]);

        $pastryCat = Category::create([
            'restaurant_id' => $restaurant2->id,
            'name' => 'Pastries',
            'icon' => '🥐',
            'sort_order' => 2,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant2->id,
            'category_id' => $coffeeCat->id,
            'name' => 'Cappuccino',
            'description' => 'Espresso with steamed milk foam',
            'price' => 4.50,
            'is_available' => true,
            'sort_order' => 1,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant2->id,
            'category_id' => $coffeeCat->id,
            'name' => 'Latte',
            'description' => 'Smooth espresso with steamed milk',
            'price' => 4.75,
            'is_available' => true,
            'sort_order' => 2,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant2->id,
            'category_id' => $pastryCat->id,
            'name' => 'Croissant',
            'description' => 'Buttery, flaky French croissant',
            'price' => 3.50,
            'is_available' => true,
            'sort_order' => 1,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant2->id,
            'category_id' => $pastryCat->id,
            'name' => 'Blueberry Muffin',
            'description' => 'Freshly baked muffin with blueberries',
            'price' => 3.25,
            'discount_price' => 2.75,
            'is_available' => true,
            'is_featured' => true,
            'sort_order' => 2,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            Table::create([
                'restaurant_id' => $restaurant2->id,
                'table_number' => (string) $i,
                'capacity' => 4,
                'status' => 'available',
            ]);
        }
    }
}
