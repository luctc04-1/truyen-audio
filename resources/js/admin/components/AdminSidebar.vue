<template>
  <aside class="app-menu navbar-menu" :class="{ 'sidebar-open': open }">
    <div class="navbar-brand-box">
      <router-link to="/admin" class="logo logo-dark">
        <span class="logo-sm">
          <span class="brand-mark">TA</span>
        </span>
        <span class="logo-lg">
          <strong class="brand-text">Truyen Audio</strong>
        </span>
      </router-link>
      <router-link to="/admin" class="logo logo-light">
        <span class="logo-sm">
          <span class="brand-mark">TA</span>
        </span>
        <span class="logo-lg">
          <strong class="brand-text">Truyen Audio</strong>
        </span>
      </router-link>
      <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover d-lg-none" @click="$emit('close')">
        <i class="ri-close-line"></i>
      </button>
    </div>

    <div id="scrollbar">
      <div class="container-fluid">
        <ul class="navbar-nav" id="navbar-nav">
          <li class="menu-title"><span>Menu</span></li>

          <li v-for="item in primaryItems" :key="item.id" class="nav-item">
            <router-link
              :to="item.to"
              class="nav-link menu-link"
              :class="{ active: isActive(item) }"
            >
              <i :class="item.icon"></i>
              <span>{{ item.label }}</span>
            </router-link>
          </li>

          <li v-for="group in menuGroups" :key="group.id" class="nav-item">
            <a
              href="#"
              class="nav-link menu-link is-parent"
              role="button"
              :aria-expanded="isGroupExpanded(group.id) ? 'true' : 'false'"
              @click.prevent="toggleGroup(group.id)"
            >
              <i :class="group.icon"></i>
              <span>{{ group.label }}</span>
            </a>
            <div class="collapse menu-dropdown" :class="{ show: isGroupExpanded(group.id) }">
              <ul class="nav nav-sm flex-column">
                <li v-for="child in group.children" :key="child.id" class="nav-item">
                  <router-link
                    :to="child.to"
                    class="nav-link"
                    :class="{ active: isActive(child) }"
                  >
                    {{ child.label }}
                  </router-link>
                </li>
              </ul>
            </div>
          </li>

          <li v-for="item in secondaryItems" :key="item.id" class="nav-item">
            <router-link
              :to="item.to"
              class="nav-link menu-link"
              :class="{ active: isActive(item) }"
            >
              <i :class="item.icon"></i>
              <span>{{ item.label }}</span>
            </router-link>
          </li>
        </ul>
      </div>
    </div>

    <div class="sidebar-background"></div>
  </aside>

  <div v-if="open" class="vertical-overlay" @click="$emit('close')"></div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  primaryItems: {
    type: Array,
    required: true,
  },
  secondaryItems: {
    type: Array,
    default: () => [],
  },
  menuGroups: {
    type: Array,
    required: true,
  },
  open: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['close'])

const route = useRoute()
const expandedGroups = ref({})

const parseTo = (to) => {
  const [path, queryString] = to.split('?')
  const query = {}
  if (queryString) {
    new URLSearchParams(queryString).forEach((value, key) => {
      query[key] = value
    })
  }
  return { path, query }
}

const isActive = (item) => {
  const { path, query } = parseTo(item.to)

  if (path === '/admin') {
    return route.path === '/admin' || route.name === 'AdminDashboard'
  }

  if (route.path !== path && !route.path.startsWith(`${path}/`)) {
    return false
  }

  const queryKeys = Object.keys(query)
  if (queryKeys.length > 0) {
    return queryKeys.every((key) => String(route.query[key] ?? '') === query[key])
  }

  if (path === '/admin/series' && route.query.is_hot !== undefined) {
    return false
  }

  return route.path === path || route.path.startsWith(`${path}/`)
}

const isGroupActive = (group) => group.children.some((child) => isActive(child))

const isGroupExpanded = (groupId) => Boolean(expandedGroups.value[groupId])

const toggleGroup = (groupId) => {
  if (expandedGroups.value[groupId]) {
    expandedGroups.value = { ...expandedGroups.value, [groupId]: false }
    return
  }
  expandedGroups.value = { [groupId]: true }
}

const syncExpandedGroups = () => {
  const activeGroup = props.menuGroups.find((group) => isGroupActive(group))
  expandedGroups.value = activeGroup ? { [activeGroup.id]: true } : {}
}

watch(() => route.fullPath, syncExpandedGroups, { immediate: true })
</script>
