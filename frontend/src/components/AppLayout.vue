<template>
  <n-layout has-sider>
    <n-layout-sider
      bordered
      collapse-mode="width"
      :collapsed-width="64"
      :width="240"
      :collapsed="collapsed"
      show-trigger
      @collapse="collapsed = true"
      @expand="collapsed = false"
    >
      <n-menu
        :collapsed="collapsed"
        :collapsed-width="64"
        :collapsed-icon-size="22"
        :options="menuOptions"
        :value="activeKey"
        @update:value="handleMenuSelect"
      />
    </n-layout-sider>

    <n-layout>
      <n-layout-header bordered class="header">
        <div class="header-content">
          <h2 class="header-title">Sistema de Vendas e Comissões</h2>
          <div class="header-actions">
            <n-dropdown :options="userMenuOptions" @select="handleUserMenuSelect">
              <n-button text>
                <template #icon>
                  <n-icon>
                    <svg viewBox="0 0 24 24">
                      <path
                        fill="currentColor"
                        d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z"
                      />
                    </svg>
                  </n-icon>
                </template>
                {{ authStore.user?.name }}
              </n-button>
            </n-dropdown>
          </div>
        </div>
      </n-layout-header>

      <n-layout-content class="content">
        <div class="content-wrapper">
          <router-view />
        </div>
      </n-layout-content>
    </n-layout>
  </n-layout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, h } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { NIcon, useMessage, type MenuOption } from 'naive-ui'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const message = useMessage()
const authStore = useAuthStore()

const collapsed = ref(false)

const activeKey = computed(() => route.name as string)

const renderIcon = (icon: string) => {
  return () =>
    h(NIcon, null, {
      default: () =>
        h(
          'svg',
          {
            viewBox: '0 0 24 24',
          },
          [
            h('path', {
              fill: 'currentColor',
              d: icon,
            }),
          ],
        ),
    })
}

const menuOptions = computed((): MenuOption[] => {
  const options: MenuOption[] = [
    {
      label: 'Dashboard',
      key: 'Dashboard',
      to: '/',
      icon: renderIcon('M13,3V9H21V3M13,21H21V11H13M3,21H11V15H3M3,13H11V3H3V13Z'),
    },
  ]

  if (authStore.hasPermission('view_sellers')) {
    options.push({
      label: 'Vendedores',
      key: 'sellers',
      to: '/sellers',
      icon: renderIcon(
        'M16,4C18.11,4 19.8,5.69 19.8,7.8C19.8,9.91 18.11,11.6 16,11.6C13.89,11.6 12.2,9.91 12.2,7.8C12.2,5.69 13.89,4 16,4M16,13.4C18.67,13.4 24,14.73 24,17.4V20H8V17.4C8,14.73 13.33,13.4 16,13.4M8.8,7.8C8.8,9.91 7.11,11.6 5,11.6C2.89,11.6 1.2,9.91 1.2,7.8C1.2,5.69 2.89,4 5,4C7.11,4 8.8,5.69 8.8,7.8M5,13.4C7.67,13.4 13,14.73 13,17.4V20H0V17.4C0,14.73 5.33,13.4 5,13.4Z',
      ),
      children: [
        {
          label: 'Listar Vendedores',
          key: 'Sellers',
          to: '/sellers',
        },
        ...(authStore.hasPermission('create_sellers')
          ? [
              {
                label: 'Cadastrar Vendedor',
                key: 'CreateSeller',
                to: '/sellers/create',
              },
            ]
          : []),
      ],
    })
  }

  if (authStore.hasPermission('view_sales')) {
    options.push({
      label: 'Vendas',
      key: 'sales',
      to: '/sales',
      icon: renderIcon(
        'M7,15H9C9,16.08 10.37,17 12,17C13.63,17 15,16.08 15,15C15,13.9 13.96,13.5 11.76,12.97C9.64,12.44 7,11.78 7,9C7,7.21 8.47,5.69 10.5,5.18V3H13.5V5.18C15.53,5.69 17,7.21 17,9H15C15,7.92 13.63,7 12,7C10.37,7 9,7.92 9,9C9,10.1 10.04,10.5 12.24,11.03C14.36,11.56 17,12.22 17,15C17,16.79 15.53,18.31 13.5,18.82V21H10.5V18.82C8.47,18.31 7,16.79 7,15Z',
      ),
      children: [
        {
          label: 'Listar Vendas',
          key: 'Sales',
          to: '/sales',
        },
        ...(authStore.hasPermission('create_sales')
          ? [
              {
                label: 'Cadastrar Venda',
                key: 'CreateSale',
                to: '/sales/create',
              },
            ]
          : []),
      ],
    })
  }

  return options
})

const userMenuOptions = [
  {
    label: 'Sair',
    key: 'logout',
    icon: renderIcon(
      'M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z',
    ),
  },
]

const handleMenuSelect = (key: string) => {
  const option = menuOptions.value.find((opt) => opt.key === key)
  if (option && 'to' in option && option.to) {
    router.push(option.to)
    return
  }

  for (const opt of menuOptions.value) {
    if (opt.children) {
      const child = opt.children.find((c) => 'key' in c && c.key === key)
      if (child && 'to' in child && child.to) {
        router.push(child.to)
        return
      }
    }
  }
}

const handleUserMenuSelect = async (key: string) => {
  if (key === 'logout') {
    await authStore.logout()
    message.success('Logout realizado com sucesso!')
    router.push('/login')
  }
}

onMounted(() => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
  }
})
</script>

<style scoped>
.header {
  padding: 0 24px;
  height: 64px;
  display: flex;
  align-items: center;
}

.header-content {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-title {
  margin: 0;
  color: #333;
  font-weight: 600;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.content {
  padding: 24px;
  min-height: calc(100vh - 64px);
}

.content-wrapper {
  max-width: 1200px;
  margin: 0 auto;
}
</style>
