<template>
  <div class="skeleton-table-wrapper">
    <table class="skeleton-table">
      <thead>
        <tr>
          <th v-for="c in columns" :key="c" :style="{ width: getColWidth(c) }">
            <div class="skeleton-bone bone-th"></div>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="r in rows" :key="r">
          <td v-for="c in columns" :key="c">
            <div v-if="c === 1" class="skeleton-cell-profile">
              <div class="skeleton-bone bone-thumb"></div>
              <div class="skeleton-cell-lines">
                <div class="skeleton-bone bone-title"></div>
                <div class="skeleton-bone bone-subtitle"></div>
              </div>
            </div>
            <div v-else-if="c === columns" class="skeleton-cell-actions">
              <div class="skeleton-bone bone-icon"></div>
              <div class="skeleton-bone bone-icon"></div>
            </div>
            <div v-else class="skeleton-bone bone-text" :style="{ width: getCellWidth(r, c) }"></div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  columns: {
    type: Number,
    default: 6,
  },
  rows: {
    type: Number,
    default: 6,
  },
})

function getColWidth(col) {
  if (col === 1) return '35%'
  return 'auto'
}

function getCellWidth(row, col) {
  const widths = ['60%', '80%', '45%', '70%', '50%']
  return widths[(row + col) % widths.length]
}
</script>

<style scoped>
.skeleton-table-wrapper {
  width: 100%;
  overflow-x: auto;
}

.skeleton-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.skeleton-table th {
  padding: 14px 20px;
  background: rgba(255, 255, 255, 0.02);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.skeleton-table td {
  padding: 14px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  vertical-align: middle;
}

.skeleton-bone {
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0.03) 0%,
    rgba(168, 85, 247, 0.12) 50%,
    rgba(255, 255, 255, 0.03) 100%
  );
  background-size: 200% 100%;
  animation: skeletonShimmer 1.6s infinite ease-in-out;
  border-radius: 6px;
}

@keyframes skeletonShimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.bone-th {
  height: 12px;
  width: 60%;
  border-radius: 4px;
}

.skeleton-cell-profile {
  display: flex;
  align-items: center;
  gap: 14px;
}

.bone-thumb {
  width: 42px;
  height: 56px;
  border-radius: 8px;
  flex-shrink: 0;
}

.skeleton-cell-lines {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.bone-title {
  height: 14px;
  width: 80%;
  border-radius: 4px;
}

.bone-subtitle {
  height: 10px;
  width: 50%;
  border-radius: 4px;
}

.bone-text {
  height: 14px;
  border-radius: 4px;
}

.skeleton-cell-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.bone-icon {
  width: 34px;
  height: 34px;
  border-radius: 8px;
}
</style>
