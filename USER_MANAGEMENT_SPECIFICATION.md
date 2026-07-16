# User Management Module Specification

## 1. Module Overview
The User Management module in ERP Bio Pilar serves as the centralized hub for administering user access, security policies, and organizational hierarchies. 
Authentication (verifying who a user is), authorization (verifying what a user can do), and access control are separated from core business modules to ensure a highly decoupled, secure, and scalable architecture. This separation allows security policies to be updated independently without affecting the business logic of modules like Sales, Purchasing, or Production.

## 2. Menu Structure
The User Management module will be nested under a dedicated Settings or Administration menu to restrict access to authorized personnel only.

`	ext
Settings
¦
+-- User Accounts
+-- Roles
+-- Permissions
+-- Activity Logs
+-- Profile
`

## 3. User Accounts
The User Accounts section manages the lifecycle of all employees and users who access the ERP.

### List User
**Columns displayed in the DataTable:**
- Photo
- Employee ID
- Full Name
- Username
- Email
- Role
- Department
- Position
- Status
- Last Login
- Created At
- Action

**Action Buttons:**
- View
- Edit
- Reset Password
- Activate
- Deactivate
- Delete

### Add User
**Fields:**
- Full Name
- Username
- Email
- Password
- Confirm Password
- Employee ID
- Department
- Position
- Phone Number
- Role (Dropdown)
- Profile Photo (File upload)
- Status (Active/Inactive)

### View User
Displays all information in a read-only layout (e.g., a Card with a profile header).

### Edit User
**Editable fields:** 
Full Name, Email, Department, Position, Phone Number, Role, Profile Photo, Status.
**Non-editable fields:** 
Username, Employee ID (typically locked after creation to preserve data integrity).

### Delete User
- Utilizes **Soft Delete** to ensure historical data (like who created a specific Sales Order) remains intact.
- A Confirmation dialog must be presented before deletion.

## 4. Roles
Role Management allows administrators to group permissions and assign them to users collectively rather than individually.

### CRUD
- **Create**: Add a new role.
- **Read**: List roles and view assigned permissions.
- **Update**: Edit role name, description, and toggle permissions.
- **Delete**: Soft delete roles (cannot delete roles that are currently assigned to active users).

### Fields
- Role Name
- Description
- Status (Active/Inactive)

### Example Roles
- Administrator
- Finance
- Purchasing
- Production
- Inventory
- Sales
- Project Manager
- Director
- Supervisor
- Warehouse
- HR

## 5. Permissions (Menu-Based)
The permission system is strictly **Menu-Based**. Every menu in the ERP has its own independent access rights.

### Permission Structure
Every menu is controlled by the following granular permissions:
- **Visible**: Determines whether the menu is visible in the sidebar.
- **View**: Can view the records within the menu.
- **Create**: Can create new records.
- **Edit**: Can modify existing records.
- **Delete**: Can delete or soft-delete records.
- **Approve**: Can approve workflows (e.g., PO approval).
- **Export**: Can export data to Excel/CSV.
- **Print**: Can print documents (e.g., PDF generation).

Each permission is toggled individually (? for ON, ? for OFF).

### Permission Examples
**Products Menu**
Visible   : ?
View      : ?
Create    : ?
Edit      : ?
Delete    : ?
Approve   : ?
Export    : ?
Print     : ?

**Purchase Order Menu**
Visible   : ?
View      : ?
Create    : ?
Edit      : ?
Delete    : ?
Approve   : ?
Export    : ?
Print     : ?

**Asset Reports Menu**
Visible   : ?
View      : ?
Create    : ?
Edit      : ?
Delete    : ?
Approve   : ?
Export    : ?
Print     : ?

## 6. Complete Menu Permission Matrix
Below is the complete list of all ERP Menus that must be included in the permission matrix for every Role:

| Menu Group | Menu Name | Visible | View | Create | Edit | Delete | Approve | Export | Print |
|------------|-----------|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **Dashboard** | Dashboard | ? | ? | ? | ? | ? | ? | ? | ? |
| **Master Data** | Products | ? | ? | ? | ? | ? | ? | ? | ? |
| | Suppliers | ? | ? | ? | ? | ? | ? | ? | ? |
| | Customers | ? | ? | ? | ? | ? | ? | ? | ? |
| | Units | ? | ? | ? | ? | ? | ? | ? | ? |
| | Projects | ? | ? | ? | ? | ? | ? | ? | ? |
| | Warehouses | ? | ? | ? | ? | ? | ? | ? | ? |
| **Purchasing** | Purchase Order | ? | ? | ? | ? | ? | ? | ? | ? |
| | Goods Receipt | ? | ? | ? | ? | ? | ? | ? | ? |
| | Purchase Payment | ? | ? | ? | ? | ? | ? | ? | ? |
| | Accounts Payable | ? | ? | ? | ? | ? | ? | ? | ? |
| **Production** | Bill of Material | ? | ? | ? | ? | ? | ? | ? | ? |
| | Production Order | ? | ? | ? | ? | ? | ? | ? | ? |
| | Project Production | ? | ? | ? | ? | ? | ? | ? | ? |
| **Sales** | Sales Order | ? | ? | ? | ? | ? | ? | ? | ? |
| | Sales Invoice | ? | ? | ? | ? | ? | ? | ? | ? |
| | Sales Payment | ? | ? | ? | ? | ? | ? | ? | ? |
| | Accounts Receivable | ? | ? | ? | ? | ? | ? | ? | ? |
| **Project Report**| Project Report | ? | ? | ? | ? | ? | ? | ? | ? |
| **Inventory** | Product Stock | ? | ? | ? | ? | ? | ? | ? | ? |
| | Stock Transfer | ? | ? | ? | ? | ? | ? | ? | ? |
| | Stock Opname | ? | ? | ? | ? | ? | ? | ? | ? |
| | Item Journal | ? | ? | ? | ? | ? | ? | ? | ? |
| **Asset Mgmt** | Asset Dashboard | ? | ? | ? | ? | ? | ? | ? | ? |
| | Master Categories | ? | ? | ? | ? | ? | ? | ? | ? |
| | Master Assets | ? | ? | ? | ? | ? | ? | ? | ? |
| | Asset Reports | ? | ? | ? | ? | ? | ? | ? | ? |
| **User Mgmt** | User Management | ? | ? | ? | ? | ? | ? | ? | ? |

*(Note: The checkmarks in the table above represent the toggle state for a specific Role setup).*

## 7. Role Assignment Rules
- **Collection of Permissions**: Every Role acts as a predefined collection of menu permissions.
- **Individual Control**: Administrators can enable (ON) or disable (OFF) each permission column for every menu individually.
- **Inheritance**: Permissions are strictly inherited by every user assigned to that Role.
- **Live Updates**: Changing a Role's permission matrix automatically and immediately updates the access rights for all users actively assigned to that role.

## 8. User Status
Defines the current lifecycle state of a user account.
- **Active**: Normal access permitted.
- **Inactive**: Access revoked, but the account is preserved (e.g., employee on long leave).
- **Suspended**: Temporarily blocked due to policy violations.
- **Locked**: Automatically blocked due to excessive failed login attempts.
- **Deleted (Soft Delete)**: Account removed from active lists but kept in the database for historical audit trails.

**Business Rules**: Only Active users can log in. Roles assigned to Inactive/Suspended users are temporarily nullified during session checks.

## 9. Password Policy
To ensure robust security, the following password rules apply:
- **Minimum length**: 8 characters
- **Uppercase**: At least one uppercase letter (A-Z)
- **Lowercase**: At least one lowercase letter (a-z)
- **Number**: At least one numeric digit (0-9)
- **Special Character**: At least one special character (e.g., !@#$%^&*)
- **Password Expiration (optional)**: Enforce password change every 90 days.
- **Reset Password flow**: Admin can trigger a password reset link sent to the user's email, or reset it manually to a temporary default password.

## 10. Login Security
- **Account lock**: Lock account for 15 minutes after 5 consecutive failed login attempts.
- **Remember Me**: Allows session persistence across browser restarts.
- **Last Login**: Timestamp of the most recent successful authentication.
- **Last IP Address**: Records the IP address used during the last successful login.
- **Session Timeout**: Automatic logout after 30 minutes of inactivity.

## 11. Activity Log
Every significant action within the User Management module must be logged for compliance and security auditing.
**Examples:**
- Login / Logout
- Create User
- Edit User
- Delete User
- Reset Password
- Change Role
- Deactivate Account

## 12. Database Design
Required tables for the module (conceptual design only, no migration code):
- users: Stores core user data, status, and authentication credentials.
- oles: Stores role definitions.
- permissions: Stores granular permission flags.
- ole_permissions: Pivot table linking roles to specific permissions.
- user_roles: Pivot table linking users to roles (supports multiple roles per user if needed).
- ctivity_logs: Stores system-wide user actions and login history.
- password_resets: Tracks password reset tokens and expiration.

## 13. Relationships
Entity Relationship flow:
`	ext
Users 
  ? (Many-to-Many)
Roles 
  ? (Many-to-Many)
Permissions 
  ? (Grouped by Menu)
Modules
`

## 14. Validation Rules
- **Full Name**: Required, String, Max 255.
- **Username**: Required, String, Unique (users table), Alpha-numeric, Max 50.
- **Email**: Required, valid Email format, Unique (users table).
- **Password**: Required on creation, matching Confirm Password, obeys Password Policy.
- **Employee ID**: Required, Unique.
- **Role**: Required, must exist in oles table.
- **Status**: Required, strictly mapped to allowed status enum.
- **Profile Photo**: Optional, Image (jpeg, png, jpg), Max 2MB.

## 15. Search & Filter
The User List should be highly searchable and filterable.
- **Search by**: Name, Email, Username, Employee ID.
- **Filter by**: Department, Role, Status, Created Date range, Last Login Date.

## 16. Export
The User List must support exporting the current filtered view for reporting purposes.
- **PDF**
- **Excel**
- **CSV**

## 17. Audit Trail
To track data mutations, the system must record:
- **Who**: The user performing the action.
- **When**: Timestamp of the action.
- **What Changed**: The entity and field that was modified.
- **Old Value**: State before modification.
- **New Value**: State after modification.

## 18. UI Recommendation
- **Role Detail Page (Permission Matrix)**: 
  - The Role Detail page MUST display permissions as a large matrix grouped by module sections (Dashboard, Master Data, Purchasing, Production, Sales, Inventory, Asset Management, User Management).
  - Each row represents one menu.
  - Each column represents one permission (Visible, View, Create, Edit, Delete, Approve, Export, Print).
  - Every permission should be controlled using **Toggle Switches** or **Checkboxes**. 
  - This interface should closely resemble enterprise permission management found in commercial ERP systems (SAP, Odoo, or Microsoft Dynamics 365).
- **Page Layouts**: Clean, modern, ERP-style full-width layouts without excessive whitespace.
- **Cards**: Use Bootstrap 5 Cards with subtle shadows (shadow-sm) to separate sections like User Details, Roles, and Logs.
- **DataTables**: Utilize DataTables for sorting, pagination, and instant searching.
- **Modal**: Use modals for quick actions like Assign Role or Reset Password.
- **Confirmation Dialog**: SweetAlert2 for Delete or Deactivate confirmations.
- **Badges / Status Colors**: 
  - Active: g-success
  - Inactive: g-secondary
  - Suspended/Locked: g-warning
  - Deleted: g-danger
- **Dark Mode Support**: Ensure all tables, cards, and text colors are fully compatible with the ERP's dark theme variables.

## 19. Future Enhancements
Reserve space and architectural considerations for:
- Two Factor Authentication (2FA)
- Google Login / Microsoft Login integration
- Single Sign-On (SSO) (e.g., via SAML or OAuth2)
- Permission Templates (Quickly applying pre-set permissions to new roles)
- API Token management (For mobile app or third-party integration)
