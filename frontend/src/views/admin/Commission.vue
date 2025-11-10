<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-4">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
          <h3 class="text-xl font-semibold">Commission Management</h3>
          <p class="text-sm text-muted">Track staff performance, review payouts, and understand the commission logic.</p>
        </div>
        <div class="flex items-center gap-2 bg-black/5 rounded-lg p-1 self-start">
          <button
            :class="tabButtonClass('summary')"
            @click="activeTab = 'summary'"
            type="button"
          >
            Overview
          </button>
          <button
            :class="tabButtonClass('history')"
            @click="activeTab = 'history'"
            type="button"
          >
            Transactions
          </button>
          <button
            :class="tabButtonClass('guide')"
            @click="activeTab = 'guide'"
            type="button"
          >
            How It Works
          </button>
        </div>
      </div>

      <div v-if="activeTab === 'summary'" class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2">
          <label class="text-xs text-muted uppercase">From</label>
          <input type="date" v-model="filters.from" class="h-9 rounded border border-black/10 px-2 text-sm" />
        </div>
        <div class="flex items-center gap-2">
          <label class="text-xs text-muted uppercase">To</label>
          <input type="date" v-model="filters.to" class="h-9 rounded border border-black/10 px-2 text-sm" />
        </div>
        <select v-model="filters.released" class="h-9 rounded border border-black/10 px-2 text-sm">
          <option value="all">All points</option>
          <option value="pending">Pending payout</option>
          <option value="released">Released / paid</option>
        </select>
        <Button size="sm" :disabled="loadingSummary" @click="loadSummary">
          <span v-if="loadingSummary" class="animate-spin inline-block h-4 w-4 border-2 border-white/80 border-t-transparent rounded-full mr-2"></span>
          Refresh
        </Button>
      </div>
    </div>

    <template v-if="activeTab === 'summary'">
      <Card>
        <template #header>
          <div class="text-sm font-medium text-muted">Overall Summary</div>
        </template>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <div class="rounded-xl border border-primary/20 bg-primary/5 px-4 py-3">
            <div class="text-xs text-muted uppercase">Pending Amount</div>
            <div class="text-xl font-semibold mt-1 text-primary">RM {{ formatCurrency(summary.totals.pending_amount) }}</div>
            <div class="text-xs text-muted mt-1">{{ summary.totals.pending_points }} pts</div>
          </div>
          <div class="rounded-xl border border-black/10 bg-white px-4 py-3">
            <div class="text-xs text-muted uppercase">Released Amount</div>
            <div class="text-xl font-semibold mt-1">RM {{ formatCurrency(summary.totals.released_amount) }}</div>
            <div class="text-xs text-muted mt-1">{{ summary.totals.released_points }} pts</div>
          </div>
          <div class="rounded-xl border border-black/10 bg-white px-4 py-3">
            <div class="text-xs text-muted uppercase">Total Commission</div>
            <div class="text-xl font-semibold mt-1">RM {{ formatCurrency(summary.totals.total_amount) }}</div>
            <div class="text-xs text-muted mt-1">{{ summary.totals.total_points }} pts</div>
          </div>
          <div class="rounded-xl border border-black/10 bg-white px-4 py-3">
            <div class="text-xs text-muted uppercase">Staff Included</div>
            <div class="text-xl font-semibold mt-1">{{ summary.totals.staff_count }}</div>
            <div class="text-xs text-muted mt-1">Rate: RM {{ rate.toFixed(2) }} / point</div>
          </div>
        </div>
      </Card>

      <Card>
        <template #header>
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm font-medium text-muted">Staff Performance</div>
              <div class="text-xs text-muted">Click “View details” to review applications and release commission.</div>
            </div>
            <div v-if="loadingSummary" class="text-xs text-muted">Refreshing...</div>
          </div>
        </template>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-muted border-b border-black/5">
                <th class="px-3 py-2">Staff</th>
                <th class="px-3 py-2">Applications</th>
                <th class="px-3 py-2 text-right">Total Points</th>
                <th class="px-3 py-2 text-right">Pending Points</th>
                <th class="px-3 py-2 text-right">Pending Amount (RM)</th>
                <th class="px-3 py-2 text-right">Released Amount (RM)</th>
                <th class="px-3 py-2 text-right">Last Credited</th>
                <th class="px-3 py-2 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!loadingSummary && summary.data.length === 0">
                <td colspan="8" class="px-3 py-6 text-center text-sm text-muted">No commission data available for the selected filters.</td>
              </tr>
              <tr v-for="row in summary.data" :key="row.staff_id" class="border-b border-black/5 hover:bg-black/5 transition-colors">
                <td class="px-3 py-2">
                  <div class="font-medium text-text">{{ row.name }}</div>
                  <div class="text-xs text-muted">{{ row.email }}</div>
                </td>
                <td class="px-3 py-2">{{ row.records_count }}</td>
                <td class="px-3 py-2 text-right">{{ row.total_points }}</td>
                <td class="px-3 py-2 text-right text-warning">{{ row.pending_points }}</td>
                <td class="px-3 py-2 text-right text-warning">RM {{ formatCurrency(row.pending_amount) }}</td>
                <td class="px-3 py-2 text-right text-success">RM {{ formatCurrency(row.released_amount) }}</td>
                <td class="px-3 py-2 text-right text-xs text-muted">{{ row.last_credited_at ? formatDate(row.last_credited_at) : '—' }}</td>
                <td class="px-3 py-2 text-right">
                  <Button size="sm" variant="ghost" @click="openStaffDetail(row)">View details</Button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </Card>
    </template>

    <template v-else-if="activeTab === 'history'">
      <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2">
          <label class="text-xs text-muted uppercase">From</label>
          <input type="date" v-model="historyFilters.from" class="h-9 rounded border border-black/10 px-2 text-sm" />
        </div>
        <div class="flex items-center gap-2">
          <label class="text-xs text-muted uppercase">To</label>
          <input type="date" v-model="historyFilters.to" class="h-9 rounded border border-black/10 px-2 text-sm" />
        </div>
        <select v-model="historyFilters.released" class="h-9 rounded border border-black/10 px-2 text-sm">
          <option value="all">All statuses</option>
          <option value="pending">Pending payout</option>
          <option value="released">Released</option>
        </select>
        <input
          v-model="historyFilters.staffId"
          type="text"
          placeholder="Staff ID"
          class="h-9 rounded border border-black/10 px-2 text-sm"
        />
        <Button size="sm" :disabled="historyLoading" @click="loadHistory">
          <span v-if="historyLoading" class="animate-spin inline-block h-4 w-4 border-2 border-white/80 border-t-transparent rounded-full mr-2"></span>
          Apply filters
        </Button>
      </div>

      <Card>
        <template #header>
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm font-medium text-muted">Commission Transactions</div>
              <div class="text-xs text-muted">Released and pending records across all staff.</div>
            </div>
            <div v-if="historyLoading" class="text-xs text-muted">Loading...</div>
          </div>
        </template>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-muted border-b border-black/5">
                <th class="px-3 py-2">Staff</th>
                <th class="px-3 py-2">Application</th>
                <th class="px-3 py-2">Reason</th>
                <th class="px-3 py-2 text-right">Points</th>
                <th class="px-3 py-2 text-right">Amount (RM)</th>
                <th class="px-3 py-2 text-right">Credited</th>
                <th class="px-3 py-2 text-right">Released</th>
                <th class="px-3 py-2 text-right">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!historyLoading && historyRows.length === 0">
                <td colspan="8" class="px-3 py-6 text-center text-sm text-muted">No commission records match the selected filters.</td>
              </tr>
              <tr v-for="record in historyRows" :key="record.id" class="border-b border-black/5 hover:bg-black/5 transition-colors">
                <td class="px-3 py-2">
                  <div class="font-medium text-text">{{ record.staff?.name || '—' }}</div>
                  <div class="text-xs text-muted">{{ record.staff?.email || '—' }}</div>
                </td>
                <td class="px-3 py-2">
                  <div class="font-medium">#{{ record.application_id || '—' }}</div>
                </td>
                <td class="px-3 py-2 uppercase text-xs text-muted">{{ record.reason || '—' }}</td>
                <td class="px-3 py-2 text-right">{{ record.points }}</td>
                <td class="px-3 py-2 text-right">RM {{ formatCurrency(record.points * rate) }}</td>
                <td class="px-3 py-2 text-right text-xs text-muted">{{ record.credited_at ? formatDate(record.credited_at) : '—' }}</td>
                <td class="px-3 py-2 text-right text-xs text-muted">{{ record.released_at ? formatDate(record.released_at) : '—' }}</td>
                <td class="px-3 py-2 text-right">
                  <span :class="record.released ? 'text-success' : 'text-warning'">
                    {{ record.released ? 'Released' : 'Pending' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <Pagination
          v-if="historyMeta"
          :page="historyMeta.current_page"
          :per-page="historyMeta.per_page"
          :total="historyMeta.total"
          @update:page="changeHistoryPage"
        />
      </Card>
    </template>

    <Card v-else>
      <template #header>
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm font-medium text-muted">How commission is calculated</div>
            <div class="text-xs text-muted">Use this mental model to explain payouts to your finance or operations team.</div>
          </div>
          <div class="text-xs text-muted">Rate: RM {{ rate.toFixed(2) }} / point</div>
        </div>
      </template>
      <div class="space-y-6">
        <div class="stack gap-4">
          <div class="text-sm">
            The platform awards commission points to staff whenever they complete key milestones for an application. Each point converts to cash using the current rate defined in <strong>System Settings → Commission</strong>. Once you release a record, the payout is marked as complete and excluded from pending totals.
          </div>
          <div class="grid gap-4 md:grid-cols-[repeat(3,minmax(0,1fr))]">
            <div class="rounded-xl border border-success/30 bg-success/5 p-4">
              <div class="text-xs uppercase text-success tracking-wide mb-2">1. Earn Points</div>
              <div class="text-sm text-muted">
                Staff actions (e.g., stage approvals, issuing letters) trigger point awards. The assignment service ensures load-balanced distribution across active staff.
              </div>
            </div>
            <div class="relative rounded-xl border border-primary/30 bg-primary/5 p-4">
              <div class="text-xs uppercase text-primary tracking-wide mb-2">2. Review Queue</div>
              <div class="text-sm text-muted">
                Pending points appear in this dashboard. Use date filters to focus on specific periods or staff members before approving payouts.
              </div>
              <span class="hidden md:block absolute -right-3 top-1/2 -translate-y-1/2 text-primary text-2xl">➜</span>
            </div>
            <div class="rounded-xl border border-warning/30 bg-warning/5 p-4">
              <div class="text-xs uppercase text-warning tracking-wide mb-2">3. Release &amp; Record</div>
              <div class="text-sm text-muted">
                When finance confirms payment, click <strong>Release</strong>. The entry locks, the pending totals drop, and the staff member sees it as paid.
              </div>
            </div>
          </div>
        </div>

        <div class="rounded-xl border border-black/10 bg-white p-5">
          <div class="text-sm font-medium mb-3">Decision Framework</div>
          <ul class="space-y-2 text-sm text-muted">
            <li><strong>Pending Amount</strong>: Cash the company still owes staff. Prioritize these for monthly payouts.</li>
            <li><strong>Released Amount</strong>: Completed payouts—use for auditing and finance handoffs.</li>
            <li><strong>Last Credited</strong>: Staff with old pending credits may need a manual follow-up.</li>
            <li><strong>Records Count</strong>: High count + low pending indicates healthy review cadence.</li>
          </ul>
        </div>

        <div class="rounded-xl border border-info/30 bg-info/5 p-5">
          <div class="text-sm font-medium mb-3">Tips</div>
          <div class="grid gap-4 md:grid-cols-3 text-sm text-muted">
            <div>
              <div class="font-medium text-info mb-1">Adjusting the rate</div>
              Commission rate is stored in settings. Update it before the next payout cycle to keep conversions accurate.
            </div>
            <div>
              <div class="font-medium text-info mb-1">Automated fairness</div>
              Assignment service uses workload + randomness, so points reflect the real mix of applications assigned to each staff member.
            </div>
            <div>
              <div class="font-medium text-info mb-1">Export ready</div>
              Use the filters to segment a time range, then copy the table into your finance sheet for reconciliation.
            </div>
          </div>
        </div>
      </div>
    </Card>

    <Modal :open="detailModalOpen" size="lg" @close="closeDetailModal">
      <template #header>
        <div>
          <div class="text-lg font-semibold">Commission Details</div>
          <div v-if="selectedStaff" class="text-xs text-muted mt-1">
            {{ selectedStaff.name }} · {{ selectedStaff.email }} · Pending: RM {{ formatCurrency(selectedStaff.pending_amount) }}
          </div>
        </div>
      </template>

      <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-3">
          <select v-model="detailFilters.released" class="h-9 rounded border border-black/10 px-2 text-sm" @change="handleDetailFilterChange">
            <option value="pending">Pending payout</option>
            <option value="all">All records</option>
            <option value="released">Released</option>
          </select>
          <div class="text-xs text-muted">Rate: RM {{ rate.toFixed(2) }} per point</div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-muted border-b border-black/5">
                <th class="px-3 py-2">Application</th>
                <th class="px-3 py-2">Reason</th>
                <th class="px-3 py-2 text-right">Points</th>
                <th class="px-3 py-2 text-right">Amount (RM)</th>
                <th class="px-3 py-2 text-right">Credited</th>
                <th class="px-3 py-2 text-right">Status</th>
                <th class="px-3 py-2 text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="detailLoading">
                <td colspan="7" class="px-3 py-6 text-center text-sm text-muted">Loading...</td>
              </tr>
              <tr v-else-if="detailRows.length === 0">
                <td colspan="7" class="px-3 py-6 text-center text-sm text-muted">No records found.</td>
              </tr>
              <tr v-for="record in detailRows" :key="record.id" class="border-b border-black/5 hover:bg-black/5 transition-colors">
                <td class="px-3 py-2">
                  <div class="font-medium text-text">#{{ record.application_id || '—' }}</div>
                </td>
                <td class="px-3 py-2 text-muted uppercase text-xs">{{ record.reason || '—' }}</td>
                <td class="px-3 py-2 text-right">{{ record.points }}</td>
                <td class="px-3 py-2 text-right">RM {{ formatCurrency(record.points * rate) }}</td>
                <td class="px-3 py-2 text-right text-xs text-muted">{{ record.credited_at ? formatDate(record.credited_at) : '—' }}</td>
                <td class="px-3 py-2 text-right">
                  <span :class="record.released ? 'text-success' : 'text-warning'">
                    {{ record.released ? 'Released' : 'Pending' }}
                  </span>
                </td>
                <td class="px-3 py-2 text-right">
                  <Button
                    size="sm"
                    variant="ghost"
                    :disabled="record.released || releasingId === record.id"
                    @click="releaseRecord(record)"
                  >
                    <span v-if="releasingId === record.id" class="animate-spin inline-block h-4 w-4 border-2 border-current border-t-transparent rounded-full mr-2"></span>
                    Release
                  </Button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <Pagination
          v-if="detailMeta"
          :page="detailMeta.current_page"
          :per-page="detailMeta.per_page"
          :total="detailMeta.total"
          @update:page="changeDetailPage"
        />
      </div>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import Button from '@/components/ui/Button.vue'
import Card from '@/components/ui/Card.vue'
import Modal from '@/components/ui/Modal.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { useToast } from '@/composables/useToast'
import { api } from '@/services/api'

type SummaryRow = {
  staff_id: number
  name: string
  email: string
  whatsapp?: string
  records_count: number
  total_points: number
  released_points: number
  pending_points: number
  total_amount: number
  released_amount: number
  pending_amount: number
  last_credited_at?: string | null
}

const toast = useToast()

const filters = ref<{ from: string; to: string; released: 'all' | 'pending' | 'released' }>({
  from: '',
  to: '',
  released: 'pending'
})

const summary = ref<{ data: SummaryRow[]; totals: any; rate: number }>({
  data: [],
  totals: { total_points: 0, released_points: 0, pending_points: 0, total_amount: 0, released_amount: 0, pending_amount: 0, staff_count: 0 },
  rate: 0
})

const loadingSummary = ref(false)
const detailModalOpen = ref(false)
const selectedStaff = ref<SummaryRow | null>(null)
const detailRows = ref<any[]>([])
const detailMeta = ref<any>(null)
const detailLoading = ref(false)
const detailFilters = ref<{ released: 'pending' | 'all' | 'released' }>({ released: 'pending' })
const releasingId = ref<number | null>(null)
const activeTab = ref<'summary' | 'history' | 'guide'>('summary')
const historyFilters = ref<{ from: string; to: string; released: 'all' | 'pending' | 'released'; staffId: string }>({
  from: '',
  to: '',
  released: 'all',
  staffId: ''
})
const historyRows = ref<any[]>([])
const historyMeta = ref<{ current_page: number; per_page: number; total: number } | null>(null)
const historyLoading = ref(false)
const historyLoaded = ref(false)

const rate = computed(() => summary.value.rate || 0)

function tabButtonClass(tab: 'summary' | 'history' | 'guide') {
  const isActive = activeTab.value === tab
  return [
    'h-8 rounded-md px-3 text-sm font-medium transition-colors',
    isActive ? 'bg-white text-text shadow-sm' : 'text-muted hover:text-text'
  ].join(' ')
}

function formatCurrency(value: number | string) {
  const num = Number(value || 0)
  return num.toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(value: string) {
  return new Date(value).toLocaleString()
}

function normalizePaginatedResponse(payload: any): { data: any[]; meta: { current_page: number; per_page: number; total: number } | null } {
  if (!payload) return { data: [], meta: null }
  if (Array.isArray(payload.data) && payload.meta) {
    return {
      data: payload.data,
      meta: {
        current_page: Number(payload.meta.current_page || 1),
        per_page: Number(payload.meta.per_page || payload.data.length || 0),
        total: Number(payload.meta.total || payload.data.length || 0)
      }
    }
  }
  if (Array.isArray(payload.data) && typeof payload.current_page !== 'undefined') {
    return {
      data: payload.data,
      meta: {
        current_page: Number(payload.current_page || 1),
        per_page: Number(payload.per_page || payload.data.length || 0),
        total: Number(payload.total || payload.data.length || 0)
      }
    }
  }
  if (Array.isArray(payload)) {
    return { data: payload, meta: null }
  }
  return { data: Array.isArray(payload?.data) ? payload.data : [], meta: payload?.meta || null }
}

async function loadSummary() {
  loadingSummary.value = true
  try {
    const params: Record<string, any> = {}
    if (filters.value.from) params.from = filters.value.from
    if (filters.value.to) params.to = filters.value.to
    if (filters.value.released !== 'all') {
      params.released = filters.value.released === 'released'
    }
    const result = await api.admin.staffPoints.summary(params) as any
    const data = result.data || []
    summary.value = {
      data,
      totals: result.totals || summary.value.totals,
      rate: result.rate || 0
    }
    if (selectedStaff.value) {
      const refreshed = data.find((row: SummaryRow) => row.staff_id === selectedStaff.value?.staff_id)
      if (refreshed) {
        selectedStaff.value = refreshed
      }
    }
  } catch (e: any) {
    toast.error(e?.message || 'Failed to load commission summary')
  } finally {
    loadingSummary.value = false
  }
}

function openStaffDetail(row: SummaryRow) {
  selectedStaff.value = row
  detailFilters.value.released = 'pending'
  detailModalOpen.value = true
  fetchDetailRows(1)
}

function closeDetailModal() {
  detailModalOpen.value = false
  selectedStaff.value = null
  detailRows.value = []
  detailMeta.value = null
}

function handleDetailFilterChange() {
  fetchDetailRows(1)
}

async function fetchDetailRows(page = 1) {
  if (!selectedStaff.value) return
  detailLoading.value = true
  try {
    const params: Record<string, any> = {
      staff_id: selectedStaff.value.staff_id,
      page
    }
    if (detailFilters.value.released === 'pending') {
      params.released = false
    } else if (detailFilters.value.released === 'released') {
      params.released = true
    }
    const result = await api.admin.staffPoints.list(params) as any
    const normalized = normalizePaginatedResponse(result)
    detailRows.value = normalized.data
    detailMeta.value = normalized.meta
  } catch (e: any) {
    toast.error(e?.message || 'Failed to load commission records')
  } finally {
    detailLoading.value = false
  }
}

function changeDetailPage(newPage: number) {
  fetchDetailRows(newPage)
}

async function releaseRecord(record: any) {
  if (record.released || releasingId.value) return
  try {
    releasingId.value = record.id
    await api.admin.staffPoints.release(record.id)
    toast.success('Commission released')
    record.released = true
    await Promise.all([
      loadSummary(),
      fetchDetailRows(detailMeta.value?.current_page || 1),
      activeTab.value === 'history' ? loadHistory(historyMeta.value?.current_page || 1) : Promise.resolve()
    ])
  } catch (e: any) {
    toast.error(e?.message || 'Failed to release commission')
  } finally {
    releasingId.value = null
  }
}

async function loadHistory(page = 1) {
  historyLoading.value = true
  try {
    const params: Record<string, any> = {
      page,
      per_page: 20
    }
    if (historyFilters.value.from) params.from = historyFilters.value.from
    if (historyFilters.value.to) params.to = historyFilters.value.to
    if (historyFilters.value.released !== 'all') {
      params.released = historyFilters.value.released === 'released'
    }
    const staffIdTrimmed = historyFilters.value.staffId.trim()
    if (staffIdTrimmed) {
      const numeric = Number(staffIdTrimmed)
      if (!Number.isNaN(numeric)) {
        params.staff_id = numeric
      }
    }
    const result = await api.admin.staffPoints.list(params) as any
    const normalized = normalizePaginatedResponse(result)
    historyRows.value = normalized.data
    historyMeta.value = normalized.meta
    historyLoaded.value = true
  } catch (e: any) {
    toast.error(e?.message || 'Failed to load transaction history')
  } finally {
    historyLoading.value = false
  }
}

function changeHistoryPage(newPage: number) {
  loadHistory(newPage)
}

onMounted(loadSummary)

watch(activeTab, (tab) => {
  if (tab === 'history' && !historyLoaded.value) {
    loadHistory()
  }
})
</script>

