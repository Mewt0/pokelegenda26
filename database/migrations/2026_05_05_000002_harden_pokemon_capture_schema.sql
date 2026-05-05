ALTER TABLE pok_user
  ADD PRIMARY KEY (id),
  ADD KEY idx_pok_user_owner_active (users, active, hp_my),
  ADD KEY idx_pok_user_base_level (basenum, lvl);
