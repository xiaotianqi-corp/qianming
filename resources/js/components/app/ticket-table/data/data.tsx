import {
  CheckCircle2,
  Circle,
  Clock,
  XCircle,
  ArrowDown,
  ArrowRight,
  ArrowUp,
  AlertCircle,
} from "lucide-react"

export const statuses = [
  {
    value: "open",
    label: "Open",
    icon: Circle,
    className: "bg-blue-50 text-blue-700 border-blue-200",
  },
  {
    value: "pending",
    label: "Pending",
    icon: Clock,
    className: "bg-yellow-50 text-yellow-700 border-yellow-200",
  },
  {
    value: "resolved",
    label: "Resolved",
    icon: CheckCircle2,
    className: "bg-green-50 text-green-700 border-green-200",
  },
  {
    value: "closed",
    label: "Closed",
    icon: XCircle,
    className: "bg-slate-100 text-slate-700 border-slate-300",
  },
]

export const priorities = [
  {
    value: "low",
    label: "Low",
    icon: ArrowDown,
    className: "bg-slate-100 text-slate-700 border-slate-300",
  },
  {
    value: "medium",
    label: "Medium",
    icon: ArrowRight,
    className: "bg-blue-50 text-blue-700 border-blue-200",
  },
  {
    value: "high",
    label: "High",
    icon: ArrowUp,
    className: "bg-orange-50 text-orange-700 border-orange-200",
  },
  {
    value: "urgent",
    label: "Urgent",
    icon: AlertCircle,
    className: "bg-red-50 text-red-700 border-red-200",
  },
]

export const categories = [
  { value: "identity", label: "Identity" },
  { value: "payment", label: "Payment" },
  { value: "technical", label: "Technical" },
]