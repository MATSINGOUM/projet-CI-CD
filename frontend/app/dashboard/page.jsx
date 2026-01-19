"use client";
import ProtectedRoute from "@/components/ProtectedRoute";
import Link from "next/link";

export default function Dashboard() {
  return (
    <ProtectedRoute>
      <div className="dashboard">
        <h1>Admin Dashboard</h1>
        <nav>
          <Link href="/dashboard/users">👤 Utilisateurs</Link>
          <Link href="/dashboard/accounts">🏦 Comptes bancaires</Link>
        </nav>
      </div>
    </ProtectedRoute>
  );
}
