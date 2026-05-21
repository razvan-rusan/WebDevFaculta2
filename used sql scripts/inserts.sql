USE gallery_site_db;

# hash de la 'password123'
INSERT INTO users (username, password_hash, role) VALUES
                                                      ('admin_razvan', '$2y$10$7R9GvKbeZc1nux66P7qYvO0zLwHnpxe4U8V7.tM0k9m1bJqH3K1t2', 'admin'),
                                                      ('pixel_master', '$2y$10$7R9GvKbeZc1nux66P7qYvO0zLwHnpxe4U8V7.tM0k9m1bJqH3K1t2', 'normal_user'),
                                                      ('nature_lover', '$2y$10$7R9GvKbeZc1nux66P7qYvO0zLwHnpxe4U8V7.tM0k9m1bJqH3K1t2', 'normal_user'),
                                                      ('tech_blogger', '$2y$10$7R9GvKbeZc1nux66P7qYvO0zLwHnpxe4U8V7.tM0k9m1bJqH3K1t2', 'normal_user'),
                                                      ('travel_pro', '$2y$10$7R9GvKbeZc1nux66P7qYvO0zLwHnpxe4U8V7.tM0k9m1bJqH3K1t2', 'normal_user');

INSERT INTO blogs (author_user_id, name) VALUES
                                             (1, 'Admin Announcements'),
                                             (4, 'The PHP Vanguard'),
                                             (4, 'Javascript Weekly'),
                                             (5, 'Travel Diary 2026'),
                                             (2, 'Digital Art Theory');

INSERT INTO articles (blog_id, title, content) VALUES
                                                   (1, 'Welcome', 'Welcome to our new platform! This site uses Twig and PHP.'),
                                                   (2, 'Why PHP is great', 'PHP 8.x is faster and more robust than ever.'),
                                                   (2, 'Twig vs Blade', 'A deep dive into templating engines for modern web dev.'),
                                                   (4, 'Bucharest at Night', 'A wonderful trip through the old town and Calea Victoriei.'),
                                                   (5, 'Mastering Contrast', 'How to use lighting and values in your digital painting.');

INSERT INTO galleries (author_user_id, name) VALUES
                                                 (2, 'Cyberpunk Architecture'),
                                                 (3, 'Forest Landscapes'),
                                                 (3, 'Macro Insects'),
                                                 (5, 'Transylvanian Castles'),
                                                 (1, 'System Icons');

INSERT INTO article_comments (article_id, user_id, content) VALUES
                                                       (1, 2, 'First comment! Looking forward to the updates.'),
                                                       (2, 3, 'Agreed, PHP is alive and well in 2026.'),
                                                       (2, 5, 'Can you do a follow-up on Composer best practices?'),
                                                       (4, 1, 'The architecture in the Old Town is truly unique.'),
                                                       (5, 4, 'The part about rim lighting was a game changer for me.');

INSERT INTO gallery_comments (gallery_id, content) VALUES
                                                       (1, 'The neon color palette is absolutely stunning.'),
                                                       (2, 'These forest shots make me want to go hiking.'),
                                                       (4, 'Peles Castle is definitely the highlight here.'),
                                                       (4, 'Great composition on the Bran Castle shot.'),
                                                       (5, 'Very clean icons. Did you use Illustrator?');