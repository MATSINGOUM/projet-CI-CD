"use client";
import { useState, useEffect } from "react";
import { getUsers, deleteUser } from "@/lib/api";
import UserTable from "../../components/UserTable";
import CreateUserModal from "../../components/CreateUserModal";
import UserDetailsModal from "../../components/UserDetailsModal";

export default function UsersPage() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [selectedUser, setSelectedUser] = useState(null);

  const fetchUsers = async () => {
    try {
      const token = localStorage.getItem("token");
      const data = await getUsers(token);
      setUsers(data.users || data);
      setError("");
    } catch (err) {
      setError("Erreur lors du chargement des utilisateurs");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchUsers();
  }, []);

  const handleDelete = async (userId) => {
    if (!confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?")) {
      return;
    }

    try {
      const token = localStorage.getItem("token");
      await deleteUser(userId, token);
      await fetchUsers();
    } catch (err) {
      alert("Erreur lors de la suppression de l'utilisateur");
    }
  };

  const handleUserClick = (user) => {
    setSelectedUser(user);
  };

  const handleCreateSuccess = () => {
    setShowCreateModal(false);
    fetchUsers();
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="text-gray-600">Chargement des utilisateurs...</div>
      </div>
    );
  }

  return (
    <div>
      <div className="mb-8 flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-800">Gestion des Utilisateurs</h1>
          <p className="text-gray-600 mt-2">
            Liste de tous les utilisateurs du système
          </p>
        </div>
        <button
          onClick={() => setShowCreateModal(true)}
          className="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors font-medium"
        >
          + Ajouter un utilisateur
        </button>
      </div>

      {error && (
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
          {error}
        </div>
      )}

      <div className="bg-white rounded-lg shadow">
        <UserTable 
          users={users} 
          onDelete={handleDelete}
          onUserClick={handleUserClick}
        />
      </div>

      {users.length === 0 && !error && (
        <div className="text-center py-12 text-gray-500">
          Aucun utilisateur trouvé
        </div>
      )}

      {showCreateModal && (
        <CreateUserModal
          onClose={() => setShowCreateModal(false)}
          onSuccess={handleCreateSuccess}
        />
      )}

      {selectedUser && (
        <UserDetailsModal
          user={selectedUser}
          onClose={() => setSelectedUser(null)}
        />
      )}
    </div>
  );
}