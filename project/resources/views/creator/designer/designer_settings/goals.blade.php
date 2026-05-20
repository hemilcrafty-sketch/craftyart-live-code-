@include('layouts.masterhead')

<style>
    .designer-system-container {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 20px;
    }

    .system-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .system-header {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .system-title {
        font-size: 20px;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }

    .system-subtitle {
        font-size: 14px;
        color: #6c757d;
        margin: 5px 0 0 0;
    }

    .system-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .system-table thead th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        font-size: 13px;
        padding: 15px 12px;
        border-bottom: 2px solid #dee2e6;
    }

    .system-table tbody td {
        padding: 12px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 14px;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }

    .btn-action {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        margin: 2px;
        display: inline-block;
    }

    .btn-edit {
        background: #ffc107;
        color: #212529;
    }

    .btn-toggle {
        background: #6c757d;
        color: white;
    }

    .btn-delete {
        background: #dc3545;
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .modal.show {
        display: block;
        background: rgba(0, 0, 0, 0.5);
    }
</style>

<div class="main-container">
    <div class="designer-system-container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="system-card">
            <div class="system-header">
                <div>
                    <h1 class="system-title">Designer Goals</h1>
                    <p class="system-subtitle">Manage designer motivations (Freelancer / crafty_creator)</p>
                </div>
                <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('show')">
                    <i class="fa fa-plus"></i> Add New Goal
                </button>
            </div>
            <div class="table-responsive">
                <table class="system-table table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($goals as $goal)
                            <tr>
                                <td>#{{ $goal->id }}</td>
                                <td><strong>{{ $goal->name }}</strong></td>
                                <td><code>{{ $goal->slug }}</code></td>
                                <td>{{ Str::limit($goal->description, 50) }}</td>
                                <td>{{ $goal->sort_order }}</td>
                                <td>
                                    @if($goal->is_active)
                                        <span class="status-badge status-active">Active</span>
                                    @else
                                        <span class="status-badge status-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn-action btn-edit"
                                        onclick="openEditModal({{ $goal->id }}, '{{ addslashes($goal->name) }}', '{{ addslashes($goal->description ?? '') }}', {{ $goal->sort_order }})"><i
                                            class="fa fa-edit"></i></button>
                                    <form action="{{ route('designer_system.goals.toggle', $goal->id) }}" method="POST"
                                        style="display:inline;">@csrf
                                        <button type="submit" class="btn-action btn-toggle"><i
                                                class="fa fa-toggle-{{ $goal->is_active ? 'on' : 'off' }}"></i></button>
                                    </form>
                                    <form action="{{ route('designer_system.goals.delete', $goal->id) }}" method="POST"
                                        style="display:inline;" onsubmit="return confirm('Delete this goal?');">@csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete"><i
                                                class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">No designer goals. Add one above.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('designer_system.goals.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Goal</h5>
                    <button type="button" class="close"
                        onclick="document.getElementById('addModal').classList.remove('show')">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('addModal').classList.remove('show')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST">@csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Goal</h5>
                    <button type="button" class="close"
                        onclick="document.getElementById('editModal').classList.remove('show')">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('editModal').classList.remove('show')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('layouts.masterscript')

<script>
    function openEditModal(id, name, description, sortOrder) {
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description || '';
        document.getElementById('edit_sort_order').value = sortOrder;
        document.getElementById('editForm').action = '{{ url("designer-system/goals") }}/' + id;
        document.getElementById('editModal').classList.add('show');
    }
    window.onclick = function (e) {
        if (e.target.classList.contains('modal')) e.target.classList.remove('show');
    };
</script>