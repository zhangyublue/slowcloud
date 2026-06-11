# Slowcloud

<p align="right">
  <strong>中文</strong> · <a href="./README.en.md">English</a>
</p>

Slowcloud 是一个为 Typecho 编写的轻量博客主题，视觉上偏安静、通透，适合个人博客、记录站和以文章为中心的内容站点。

示例站点：[https://slowcloud.cn](https://slowcloud.cn)

![Slowcloud 主题截图](./screenshot.png)

## 环境要求

- 基于 Typecho `1.3.0` 开发和测试。
- 建议使用 PHP `8.0` 或更高版本，最低请使用 PHP `7.4` 或更高版本。
- 请确保 Typecho 所需 PHP 扩展可用，包括 `mbstring`、`json`、`Reflection`，以及至少一种数据库扩展，例如 `mysqli`、`sqlite3`、`pgsql` 或对应的 PDO 扩展。
- 如果使用较旧的 Typecho 或 PHP 版本，可能需要自行适配主题设置、后台编辑器增强和现代 PHP 类型声明。

## 功能特性

- 首页、归档、搜索、文章详情、独立页面、404 页面和时光轴页面模板。
- 响应式三栏布局，支持隐藏侧边栏。
- Header 支持自定义 Logo、背景图、高度、站点宽度和简介文案。
- 作者信息栏支持头像、名称、简介、GitHub、Bilibili、友链和站点统计。
- 侧边栏包含最新文章、分类列表和标签云。
- 文章支持海报图字段，海报图会用于文章卡片、详情页和时光轴。
- 文章阅读量记录和展示。
- 时光轴模板按年份、月份归档文章，并展示文章数、月份数、最近更新和最早记录。
- 白天、黑夜、跟随系统三种默认主题模式，前台可手动切换。
- 后台主题设置按分组折叠展示，便于管理。
- Markdown 内容渲染样式，包括标题、表格、引用、行内代码等。
- 后台增强编辑器，支持标题级别选择、代码块快捷插入和实时预览样式。
- Prism 代码高亮，白天模式使用 Coy，黑夜模式使用 Okaidia，支持自动语言加载和行号。
- 代码块前台渲染为类 macOS 窗口样式。
- 评论区支持回复、头像、表情面板和表情短码渲染。
- 表情支持泡泡、阿鲁、颜文字和 emoji。泡泡/阿鲁以短码保存，渲染时替换为图片。
- 支持 CDN 域名配置，可改写 `usr/uploads` 图片和 slowcloud 表情图片地址。
- 支持 ICP 备案号和公安联网备案号展示。

## 安装

1. 将 `slowcloud` 目录放入 Typecho 的 `usr/themes/` 目录。
2. 在 Typecho 后台进入 `控制台 -> 外观`，启用 `slowcloud`。
3. 进入主题设置，按需配置 Logo、Header 背景、作者信息、友链、主题模式和 CDN 地址。

## 常用配置

- `浏览器 Tab 文字`：浏览器标题后缀，不填写时使用站点标题。
- `网站 Logo`：用于 favicon 和头部标识。
- `Header 背景图`：站点顶部横幅背景。
- `Header 高度`：支持 CSS 高度值，例如 `120px`、`80vh`。
- `站点主体宽度`：控制主要内容宽度。
- `主体背景色`、`左栏背景色`、`中栏背景色`、`右栏背景色`：控制页面区域背景。
- `首页简介`：显示在 Header 和作者信息兜底文案中。
- `博主头像`、`博主名称`、`博主描述`：作者信息栏内容。
- `GitHub 地址`、`Bilibili 地址`：作者信息栏社交链接。
- `友链列表`：每行一条，格式为 `名称|https://example.com`。
- `侧边栏`：控制首页、文章页和页面是否显示右侧栏。
- `默认主题模式`：可选白天、黑夜、跟随系统。
- `上传图片 CDN 地址`：填写 CDN 根域名，例如 `https://cdn.example.com`。配置后会改写上传图片和表情图片 URL。
- `ICP备案号`、`公安联网备案号`：页脚备案信息。

## 文件与目录说明

```txt
slowcloud/
	├── index.php                 # 首页、归档、搜索和列表页入口
	├── archive.php               # 归档页转发入口，复用 index.php
	├── post.php                  # 文章详情页模板，包含阅读量、海报图、正文、标签、上一篇/下一篇和评论
	├── page.php                  # 独立页面模板
	├── timeline.php              # 自定义时光轴页面模板
	├── comments.php              # 评论列表、评论表单和表情面板
	├── header.php                # 页面头部、导航、主题样式和 Prism 样式加载
	├── footer.php                # 页脚、备案信息、前台脚本和 Prism 脚本加载
	├── 404.php                   # 404 页面和搜索入口
	├── functions.php             # 主题配置、工具函数、内容渲染、CDN 改写、表情解析、阅读量、时光轴数据和编辑器增强入口
	├── style.css                 # Typecho 主题声明文件
	├── screenshot.png            # Typecho 后台主题截图
	├── README.md                 # 中文说明文档
	├── README.en.md              # 英文说明文档
	├── components/               # 可复用页面组件
	│	├── author-panel.php       # 作者信息栏，包含头像、简介、社交链接、站点统计和友链
	│	├── sidebar.php            # 右侧栏，包含最新文章、分类和标签云
	│	├── post-card.php          # 列表页文章卡片
	│	├── pagination.php         # 分页组件
	│	├── empty.php              # 无内容状态组件
	│	└── post-meta.php          # 文章元信息组件
	└── assets/                   # 静态资源目录
		├── css/                  # 样式文件
		│	├── main.css           # 主题主样式，包含布局、Header、卡片、侧边栏、评论区、表情面板、响应式和明暗主题
		│	├── content-render.css # 文章正文和编辑器预览区的 Markdown 内容样式
		│	└── code-highlight.css # Prism 代码窗口、行号和明暗主题适配样式
		├── js/                   # 前台脚本
		│	├── main.js            # 主题切换、分类折叠、评论表情插入和回复状态处理
		│	└── code-highlight.js  # 前台代码块高亮、语言识别、Prism 自动加载和代码窗口包装
		├── typecho/              # Typecho 后台增强资源
		│	├── editor-enhance.js  # 后台 Markdown 编辑器增强逻辑
		│	├── editor-enhance.css # 后台编辑器和预览区域样式
		│	└── prism/             # 本地 Prism 运行时、语言组件、自动加载插件、行号插件和 Coy/Okaidia 主题
		├── json/                 # JSON 配置资源
		│	└── slowcloud.owo.json # 泡泡、阿鲁和颜文字表情配置
		├── owo/                  # 表情图片资源
		│	├── paopao/            # 泡泡表情图片
		│	└── aru/               # 阿鲁表情图片
		├── iconfont/             # 图标字体资源，用于主题中的图标按钮和元信息图标
		└── img/                  # 默认图片资源，包括头像、Logo、Header 背景和文章占位图
```

## 备注

- 如果需要使用时光轴，请新建独立页面并选择 `时光轴页面` 模板。
- 如果需要在后台查看 PV、UV、IP 和最近访问记录，请启用 `SlowcloudStatistics` 插件，启用后可在后台的 `Slowcloud 统计` 面板查看。

## 致谢

Slowcloud 在实现和设计过程中参考了以下优秀 Typecho 主题，感谢这些项目提供的思路与启发：

- [Joe](https://github.com/HaoOuBa/Joe)
- [Kratos](https://github.com/chengzhi233/Kratos)
- [Handsome](https://www.ihewro.com/archives/489/)

## 开源协议

Slowcloud 使用 [MIT License](LICENSE) 开源。
