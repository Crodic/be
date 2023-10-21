CREATE TABLE `Role` (
  `rid` int PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(20)
);

CREATE TABLE `User` (
  `uid` int PRIMARY KEY AUTO_INCREMENT,
  `fullname` varchar(50),
  `email` varchar(100) UNIQUE,
  `password` varchar(32),
  `phone_number` varchar(18) UNIQUE,
  `address` varchar(255),
  `rid` int,
  `createdAt` datetime,
  `updatedAt` datetime,
  `isDeleted` boolean
);

CREATE TABLE `Category` (
  `cid` int PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(100)
);

CREATE TABLE `Product` (
  `pid` int PRIMARY KEY AUTO_INCREMENT,
  `cid` int,
  `title` varchar(255),
  `price` int,
  `discount` int,
  `description` longtext,
  `slug` varchar(500),
  `createdAt` datetime,
  `updatedAt` datetime,
  `isDeleted` boolean
);

CREATE TABLE `ImagesProduct` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `pid` int,
  `description` varchar(255)
);

CREATE TABLE `Cart` (
  `cart_id` int PRIMARY KEY AUTO_INCREMENT,
  `uid` int,
  `total_money` int,
  `isDeleted` boolean
);

CREATE TABLE `CartDetail` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `cart_id` int,
  `pid` int,
  `price` int,
  `quantity` int,
  `total` int
);

CREATE TABLE `Order` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `cart_id` int,
  `user_id` int,
  `fullname` varchar(100),
  `phone_number` varchar(20),
  `email` varchar(255),
  `shipping` int,
  `order_date` datetime,
  `note` longtext,
  `status` int
);

CREATE TABLE `FeedBack` (
  `fid` int PRIMARY KEY AUTO_INCREMENT,
  `firstname` varchar(30),
  `lastname` varchar(30),
  `email` varchar(100),
  `phone_number` varchar(18),
  `pid` int,
  `note` varchar(1000),
  `status` int DEFAULT 0,
  `createdAt` datetime,
  `updatedAt` datetime
);

CREATE TABLE `Ship_Units` (
  `sid` int PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255)
);

CREATE TABLE `Token` (
  `tid` int PRIMARY KEY AUTO_INCREMENT,
  `uid` int,
  `token` varchar(200)
);

ALTER TABLE `User` ADD FOREIGN KEY (`rid`) REFERENCES `Role` (`rid`);

ALTER TABLE `Product` ADD FOREIGN KEY (`cid`) REFERENCES `Category` (`cid`);

ALTER TABLE `FeedBack` ADD FOREIGN KEY (`pid`) REFERENCES `Product` (`pid`);

ALTER TABLE `ImagesProduct` ADD FOREIGN KEY (`pid`) REFERENCES `Product` (`pid`);

ALTER TABLE `Order` ADD FOREIGN KEY (`shipping`) REFERENCES `Ship_Units` (`sid`);

ALTER TABLE `CartDetail` ADD FOREIGN KEY (`cart_id`) REFERENCES `Cart` (`cart_id`);

ALTER TABLE `Order` ADD FOREIGN KEY (`cart_id`) REFERENCES `Cart` (`cart_id`);

ALTER TABLE `Order` ADD FOREIGN KEY (`user_id`) REFERENCES `User` (`uid`);

ALTER TABLE `Token` ADD FOREIGN KEY (`uid`) REFERENCES `User` (`uid`);

ALTER TABLE `Cart` ADD FOREIGN KEY (`uid`) REFERENCES `User` (`uid`);

ALTER TABLE `CartDetail` ADD FOREIGN KEY (`pid`) REFERENCES `Product` (`pid`);
