enum UserRole {
  franchisePartner('Franchise Partner'),
  farmmantraStaff('Farmmantra Staff'),
  financeDepartment('Finance Department'),
  systemAdministrator('System Administrator');

  final String displayName;
  const UserRole(this.displayName);

  bool get isFranchisePartner => this == UserRole.franchisePartner;
  bool get isFarmmantraStaff => this == UserRole.farmmantraStaff;
  bool get isFinanceDepartment => this == UserRole.financeDepartment;
  bool get isSystemAdministrator => this == UserRole.systemAdministrator;

  bool get isMobileUser =>
      this == UserRole.franchisePartner ||
      this == UserRole.farmmantraStaff ||
      this == UserRole.financeDepartment;
}
