"use client";
import { useState, useEffect } from "react";
import { getUsers, getAccounts, deactivateAccount } from "@/lib/api";
import AccountTable from "../../components/AccountTable";
import CreateAccountModal from "../../components/CreateAccountModal";

export default function AccountsPage() {
  const [accounts, setAccounts] = useState([]);
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [showModal, setShowModal] = useState(false);

  const fetchData = async () => {
    try {
      const token = localStorage.getItem("token");
      const usersData = await getUsers(token);
      const usersList = usersData.users || usersData;
      setUsers(usersList);

      // Récupérer tous les comptes pour tous les utilisateurs
      const allAccounts = [];
      for (const user of usersList) {
        try {
          const userAccounts = await getAccounts(user.id);
          const accountsWithUser = (userAccounts.accounts || userAccounts).map(acc => ({
            ...acc,
            userName: `${user.firstName} ${user.lastName}`,
            userEmail: user.email
          }));
          allAccounts.push(...accountsWithUser);
        } catch (err) {
          console.error(`Erreur pour l'utilisateur ${user.id}`, err);
        }
      }
      setAccounts(allAccounts);
      setError("");
    } catch (err) {
      setError("Erreur lors du chargement des données");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleDeactivate = async (accountId) => {
    if (!confirm("Êtes-vous sûr de vouloir désactiver ce compte ?")) {
      return;
    }

    try {
      await deactivateAccount(accountId);
      await fetchData();
    } catch (err) {
      alert("Erreur lors de la désactivation du compte");
    }
  };

  const handleCreateSuccess = () => {
    setShowModal(false);
    fetchData();
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="text-gray-600">Chargement des comptes...</div>
      </div>
    );
  }

  return (
    <div>
      <div className="mb-8 flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-800">Gestion des Comptes Bancaires</h1>
          <p className="text-gray-600 mt-2">
            Liste de tous les comptes bancaires
          </p>
        </div>
        <button
          onClick={() => setShowModal(true)}
          className="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors font-medium"
        >
          + Créer un compte
        </button>
      </div>

      {error && (
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
          {error}
        </div>
      )}

      <div className="bg-white rounded-lg shadow">
        <AccountTable accounts={accounts} onDeactivate={handleDeactivate} />
      </div>

      {accounts.length === 0 && !error && (
        <div className="text-center py-12 text-gray-500">
          Aucun compte bancaire trouvé
        </div>
      )}

      {showModal && (
        <CreateAccountModal
          users={users}
          onClose={() => setShowModal(false)}
          onSuccess={handleCreateSuccess}
        />
      )}
    </div>
  );
}