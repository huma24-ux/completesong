CREATE DATABASE sound_db;
USE sound_db;


CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    address VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE language (
    language_id INT AUTO_INCREMENT PRIMARY KEY,
    language_name VARCHAR(50) NOT NULL UNIQUE
);


CREATE TABLE genre (
    genre_id INT AUTO_INCREMENT PRIMARY KEY,
    genre_name VARCHAR(50) NOT NULL UNIQUE
);


CREATE TABLE artist (
    artist_id INT AUTO_INCREMENT PRIMARY KEY,
    artist_name VARCHAR(100) NOT NULL UNIQUE,
    artist_image VARCHAR(255),
    country VARCHAR(50),
    description TEXT
);



CREATE TABLE album (
    album_id INT AUTO_INCREMENT PRIMARY KEY,
    album_name VARCHAR(100) NOT NULL UNIQUE,
    release_year YEAR NOT NULL,
    description TEXT,
    cover_image VARCHAR(255)
);


CREATE TABLE music (
    music_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    artist_id INT NOT NULL,
    album_id INT,
    genre_id INT,
    language_id INT,
    year YEAR NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    description TEXT,
    is_new BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artist_id) REFERENCES artist(artist_id) ON DELETE CASCADE,
    FOREIGN KEY (album_id) REFERENCES album(album_id) ON DELETE SET NULL,
    FOREIGN KEY (genre_id) REFERENCES genre(genre_id) ON DELETE SET NULL,
    FOREIGN KEY (language_id) REFERENCES language(language_id) ON DELETE SET NULL
);



CREATE TABLE video (
    video_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    artist_id INT NOT NULL,
    album_id INT,
    genre_id INT,
    language_id INT,
    year YEAR NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    description TEXT,
    thumbnail_img VARCHAR(255),
    is_new BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artist_id) REFERENCES artist(artist_id) ON DELETE CASCADE,
    FOREIGN KEY (album_id) REFERENCES album(album_id) ON DELETE SET NULL,
    FOREIGN KEY (genre_id) REFERENCES genre(genre_id) ON DELETE SET NULL,
    FOREIGN KEY (language_id) REFERENCES language(language_id) ON DELETE SET NULL
);



CREATE TABLE review (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_type ENUM('music', 'video') NOT NULL,
    content_id INT NOT NULL,
    review_text TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);



CREATE TABLE rating (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_type ENUM('music', 'video') NOT NULL,
    content_id INT NOT NULL,
    rating_value TINYINT NOT NULL CHECK (rating_value BETWEEN 1 AND 5),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);



| Normal Form | Achieved? | Reason                                                                            |
| ----------- | --------- | --------------------------------------------------------------------------------- |
| **1NF**     | ✅         | Atomic columns, primary keys defined                                              |
| **2NF**     | ✅         | Non-key attributes depend on full primary key                                     |
| **3NF**     | ✅         | No transitive dependencies; categories separated (genre, language, artist, album) |



INSERT INTO language (language_name)
VALUES ('English'), ('Urdu'), ('Hindi'), ('Punjabi'), ('Spanish');


INSERT INTO genre (genre_name)
VALUES ('Pop'), ('Rock'), ('Classical'), ('Hip-Hop'), ('Folk');


INSERT INTO artist (artist_name, artist_image, country, description)
VALUES 
('Atif Aslam', 'images/atif_aslam.jpg', 'Pakistan', 'Famous Pakistani playback singer known for soulful voice.'),
('Taylor Swift', 'images/taylor_swift.jpg', 'USA', 'American singer-songwriter known for pop and country music.'),
('Arijit Singh', 'images/arijit_singh.jpg', 'India', 'Renowned Bollywood playback singer.'),
('Ali Zafar', 'images/ali_zafar.jpg', 'Pakistan', 'Singer, songwriter, actor, and painter.'),
('Ed Sheeran', 'images/ed_sheeran.jpg', 'UK', 'English singer-songwriter known for pop and acoustic songs.');



INSERT INTO album (album_name, release_year, description, cover_image)
VALUES 
('Doorie', 2006, 'Hit Pakistani album by Atif Aslam.', 'images/doorie_cover.jpg'),
('1989', 2014, 'Grammy-winning album by Taylor Swift.', 'images/1989_cover.jpg'),
('Aashiqui 2', 2013, 'Bollywood romantic album by Arijit Singh.', 'images/aashiqui2_cover.jpg'),
('Jhoom', 2011, 'Popular Pakistani pop album by Ali Zafar.', 'images/jhoom_cover.jpg'),
('Divide', 2017, 'Best-selling pop album by Ed Sheeran.', 'images/divide_cover.jpg');



INSERT INTO music 
(title, artist_id, album_id, genre_id, language_id, year, file_path, description)
VALUES
('Doorie', 1, 1, 1, 2, 2006, 'music/doorie.mp3', 'Title song from the album Doorie by Atif Aslam.'),
('Blank Space', 2, 2, 1, 1, 2014, 'music/blank_space.mp3', 'Popular track from Taylor Swift’s album 1989.'),
('Tum Hi Ho', 3, 3, 3, 3, 2013, 'music/tum_hi_ho.mp3', 'Romantic hit song from Aashiqui 2.'),
('Jhoom', 4, 4, 1, 2, 2011, 'music/jhoom.mp3', 'Melodic song from Ali Zafar’s album Jhoom.'),
('Shape of You', 5, 5, 1, 1, 2017, 'music/shape_of_you.mp3', 'Chart-topping pop song by Ed Sheeran.');



INSERT INTO video 
(title, artist_id, album_id, genre_id, language_id, year, file_path, description, thumbnail_img)
VALUES
('Doorie (Official Video)', 1, 1, 1, 2, 2006, 'videos/doorie.mp4', 'Official video of Atif Aslam’s Doorie.', 'thumbnails/doorie_thumb.jpg'),
('Blank Space (Official Video)', 2, 2, 1, 1, 2014, 'videos/blank_space.mp4', 'Music video for Taylor Swift’s Blank Space.', 'thumbnails/blank_space_thumb.jpg'),
('Tum Hi Ho (Official Video)', 3, 3, 3, 3, 2013, 'videos/tum_hi_ho.mp4', 'Romantic video from Aashiqui 2.', 'thumbnails/tum_hi_ho_thumb.jpg'),
('Jhoom (Official Video)', 4, 4, 1, 2, 2011, 'videos/jhoom.mp4', 'Official music video by Ali Zafar.', 'thumbnails/jhoom_thumb.jpg'),
('Shape of You (Official Video)', 5, 5, 1, 1, 2017, 'videos/shape_of_you.mp4', 'Official video of Ed Sheeran’s Shape of You.', 'thumbnails/shape_of_you_thumb.jpg');




| Table      | Rows Inserted | Notes                     |
| ---------- | ------------- | ------------------------- |
| `language` | 5             | Regional + English        |
| `genre`    | 5             | Popular music types       |
| `artist`   | 5             | Global + regional singers |
| `album`    | 5             | Linked properly           |
| `music`    | 5             | Foreign keys matched      |
| `video`    | 5             | Foreign keys matched      |
