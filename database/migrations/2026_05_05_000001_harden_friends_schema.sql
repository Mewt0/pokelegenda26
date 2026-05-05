ALTER TABLE friends
  MODIFY id_fr INT(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY (id_fr),
  ADD UNIQUE KEY uniq_friends_pair (id_user, id_my_friend),
  ADD KEY idx_friends_reverse_pair (id_my_friend, id_user);

ALTER TABLE friends_zayv
  MODIFY id_fz INT(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY (id_fz),
  ADD UNIQUE KEY uniq_friend_request_pair (id_user, id_user_to),
  ADD KEY idx_friend_request_to (id_user_to, id_user);
